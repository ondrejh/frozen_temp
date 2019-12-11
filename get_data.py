import urllib.request
import sqlite3
import os
import sys


DB_NAME = 'data.sql'
if os.path.isdir('/var/www/html/'):
    DB_NAME = '/var/www/html/' + DB_NAME

TABLE_NAME = 'readings'
TEMP1_NAME = 't1'
TEMP2_NAME = 't2'
TIME_STAMP_NAME = 'stamp'


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

    conn = sqlite3.connect(DB_NAME)
    c = conn.cursor()

    query = ("CREATE TABLE IF NOT EXISTS '{}' (id INTEGER PRIMARY KEY, {} REAL, {} REAL, " +
             "{} DATETIME DEFAULT CURRENT_TIMESTAMP)").format(TABLE_NAME, TEMP1_NAME, TEMP2_NAME, TIME_STAMP_NAME)
    c.execute(query)
    if stamp is None:
        query = "INSERT INTO '{}' ({}, {}) VALUES ('{}', '{}')".format(TABLE_NAME, TEMP1_NAME, TEMP2_NAME, temp1, temp2)
    else:
        query = "INSERT INTO '{}' ({}, {}, {}) VALUES ('{}', '{}', '{}')".format(TABLE_NAME, TEMP1_NAME, TEMP2_NAME,
                                                                                 TIME_STAMP_NAME, temp1, temp2, stamp)
    c.execute(query)
    conn.commit()
    conn.close()


def data_dump(time_limit=None):

    conn = sqlite3.connect(DB_NAME)
    c = conn.cursor()

    if time_limit is None:
        query = "SELECT * FROM {}".format(TABLE_NAME)
    else:
        query = "SELECT * FROM {} WHERE {} > datetime('now', '-{}')".format(TABLE_NAME, TIME_STAMP_NAME, time_limit)
    c.execute(query)
    ret = c.fetchall()

    conn.close()
    return ret


if __name__ == '__main__':

    if len(sys.argv) > 1:
        if sys.argv[1] in ('-d', '--dump'):
            if len(sys.argv) > 2:
                t_lim = sys.argv[2]
                data = data_dump(time_limit=t_lim)
            else:
                data = data_dump()
            for d in data:
                print(d)
            exit(0)

    t1, t2 = get_data()
    save_data(t1, t2)
