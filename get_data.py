#!/usr/bin/env python3

import urllib.request
import sqlite3
import os
import sys
import datetime


DB = 'data.sql'
if os.path.isdir('/var/www/html/'):
    DB = '/var/www/html/' + DB

TAB_MEASUREMENT = 'teplomer'
TAB_STATISTICS = 'statistika'
NAME_TEMP1 = 't1'
NAME_TEMP2 = 't2'
NAME_STAMP = 'stamp'
NAME_AVG = 'avg'
NAME_MAX = 'max'
NAME_MIN = 'min'


def get_data():

    fp = urllib.request.urlopen("http://89.190.88.10:89/status.html")
    fc = fp.read()
    fp.close()

    p = fc.find(b'Temperature INPUT 1')
    p += fc[p:].find(b'class="temperature"')
    p += fc[p:].find(b'>')
    in1 = fc[p+1:p+fc[p:].find(b'<')]

    p += fc[p:].find(b'Temperature INPUT 2')
    p += fc[p:].find(b'class="temperature"')
    p += fc[p:].find(b'>')
    in2 = fc[p+1:p+fc[p:].find(b'<')]

    return float(in1), float(in2)


def save_data(temp1, temp2, stamp=None):

    conn = sqlite3.connect(DB)
    c = conn.cursor()

    query = ("CREATE TABLE IF NOT EXISTS '{}' (id INTEGER PRIMARY KEY, {} REAL, {} REAL, " +
             "{} DATETIME DEFAULT CURRENT_TIMESTAMP)").format(TAB_MEASUREMENT, NAME_TEMP1, NAME_TEMP2, NAME_STAMP)
    c.execute(query)

    ret_value = False

    if stamp is None:
        query = "INSERT INTO {} ({}, {}) VALUES ('{}', '{}')".format(TAB_MEASUREMENT, NAME_TEMP1, NAME_TEMP2, temp1,
                                                                     temp2)
        c.execute(query)
        ret_value = True
    else:
        query = "SELECT {1} FROM {0} WHERE {1} = '{2}'".format(TAB_MEASUREMENT, NAME_STAMP, stamp)
        c.execute(query)
        if len(c.fetchall()) < 1:
            query = "INSERT INTO {} ({}, {}, {}) VALUES ('{}', '{}', '{}')".format(TAB_MEASUREMENT, NAME_TEMP1,
                                                                                   NAME_TEMP2, NAME_STAMP, temp1, temp2,
                                                                                   stamp)
            c.execute(query)
            ret_value = True
            print('ADD {} {} {}'.format(stamp, temp1, temp2))
        else:
            #print('SKIPPING {}'.format(stamp))
            pass

    if ret_value:
        conn.commit()
    conn.close()

    return ret_value


def data_dump(time_limit=None):

    conn = sqlite3.connect(DB)
    c = conn.cursor()

    if time_limit is None:
        query = "SELECT {1}, {2}, {3} FROM {0}".format(TAB_MEASUREMENT, NAME_STAMP, NAME_TEMP1, NAME_TEMP2)
    else:
        query = "SELECT {1}, {3}, {4} FROM {0} WHERE {1} > datetime('now', '-{2}') ORDER BY {1}".format(TAB_MEASUREMENT,
                                                                                                        NAME_STAMP,
                                                                                                        time_limit,
                                                                                                        NAME_TEMP1,
                                                                                                        NAME_TEMP2)
    c.execute(query)
    ret = c.fetchall()

    conn.close()
    return ret


def statistics_dump():

    conn = sqlite3.connect(DB)
    c = conn.cursor()

    query = "SELECT {}, {}, {}, {}, {}, {}, {} FROM {}".format(NAME_STAMP,
                                                               NAME_TEMP1 + NAME_MIN, NAME_TEMP1 + NAME_AVG,
                                                               NAME_TEMP1 + NAME_MAX, NAME_TEMP2 + NAME_MIN,
                                                               NAME_TEMP2 + NAME_AVG, NAME_TEMP2 + NAME_MAX,
                                                               TAB_STATISTICS)

    c.execute(query)
    ret = c.fetchall()

    conn.close()
    return ret


def create_statistics():

    conn = sqlite3.connect(DB)
    c = conn.cursor()

    query = "CREATE TABLE IF NOT EXISTS {} (id INT AUTO_INCREMENT PRIMARY KEY, {} FLOAT, {} FLOAT, {} FLOAT, \
{} FLOAT, {} FLOAT, {} FLOAT, {} DATETIME)".format(TAB_STATISTICS, NAME_TEMP1 + NAME_MIN, NAME_TEMP1 + NAME_AVG,
                                                   NAME_TEMP1 + NAME_MAX, NAME_TEMP2 + NAME_MIN, NAME_TEMP2 + NAME_AVG,
                                                   NAME_TEMP2 + NAME_MAX, NAME_STAMP)
    c.execute(query)

    query = "SELECT MAX({}) FROM {}".format(NAME_STAMP, TAB_STATISTICS)
    c.execute(query)
    dt_from = c.fetchone()[0]
    if dt_from is None:
        query = "SELECT MIN({}) FROM {}".format(NAME_STAMP, TAB_MEASUREMENT)
        c.execute(query)
        dt_from = c.fetchone()[0]
        dt_from = dt_from.split()[0]
    else:
        dt_from = (datetime.datetime.strptime(dt_from, "%Y-%m-%d") + datetime.timedelta(1)).strftime("%Y-%m-%d")

    query = "SELECT MAX({}) FROM {}".format(NAME_STAMP, TAB_MEASUREMENT)
    c.execute(query)
    dt_to = c.fetchone()[0].split()[0]

    print('From {} to {}'.format(dt_from, dt_to))

    dt_range = []
    while dt_from != dt_to:
        dt_range.append(dt_from)
        dt_from = (datetime.datetime.strptime(dt_from, "%Y-%m-%d") + datetime.timedelta(1)).strftime("%Y-%m-%d")
    #print(dt_range)

    cnt = 0
    for dt in dt_range:
        query = "SELECT {}, {} FROM {} WHERE DATE({}) = '{}'".format(NAME_TEMP1, NAME_TEMP2, TAB_MEASUREMENT, NAME_STAMP,
                                                                   dt)
        c.execute(query)
        res = c.fetchall()
        if len(res) > 0:
            t1min = t1max = t1avg = res[0][0]
            t2min = t2max = t2avg = res[0][1]
            if len(res) > 1:
                for row in res[1:]:
                    if t1min > row[0]:
                        t1min = row[0]
                    if t1max < row[0]:
                        t1max = row[0]
                    t1avg += row[0]
                    if t2min > row[1]:
                        t2min = row[1]
                    if t2max < row[1]:
                        t2max = row[1]
                    t2avg += row[1]
            t1avg /= len(res)
            t2avg /= len(res)

            query = "INSERT INTO {} ({}, {}, {}, {}, {}, {}, {}) VALUES ({}, {}, {}, {}, {}, {}, '{}')"
            query = query.format(TAB_STATISTICS, NAME_TEMP1 + NAME_MIN, NAME_TEMP1 + NAME_AVG, NAME_TEMP1 + NAME_MAX,
                                 NAME_TEMP2 + NAME_MIN, NAME_TEMP2 + NAME_AVG, NAME_TEMP2 + NAME_MAX, NAME_STAMP,
                                 t1min, round(t1avg, 2), t1max, t2min, round(t2avg, 2), t2max, dt)
            c.execute(query)
            print("ADD {} {} {} {} {} {} {}".format(dt, t1min, round(t1avg, 2), t1max, t2min, round(t2avg, 2), t2max))
            cnt += 1

    conn.commit()
    conn.close()

    return cnt


def copy_data_from_web():

    from copy_data_from_web import get_data as get_data_from_web

    new_data = get_data_from_web()
    cnt = 0
    for row in new_data:
        #print(row)
        if save_data(row[1], row[2], row[0]):
            cnt += 1
    if cnt > 0:
        print('{} new values copied from web.'.format(cnt))
    else:
        print('No new values found.')


if __name__ == '__main__':

    if len(sys.argv) > 1:
        if sys.argv[1] in ('-d', '--dump'):
            if len(sys.argv) > 2:
                t_lim = sys.argv[2]
                data = data_dump(time_limit=t_lim)
            else:
                data = data_dump()
            for d in sorted(data, key=lambda x: x[0]):
                print(d)
            print('{} rows.'.format(len(data)))
            exit(0)
        if sys.argv[1] in ('-D', '--dump_statistics'):
            data = statistics_dump()
            for d in sorted(data, key=lambda x: x[0]):
                print(d)
        if sys.argv[1] in ('-s', '--statistics'):
            cnt = create_statistics()
            print('{} statistic rows added.'.format(cnt))
            exit(0)
        if sys.argv[1] in ('-c', '--copy'):
            copy_data_from_web()
            exit(0)

    t1, t2 = get_data()
    save_data(t1, t2)
