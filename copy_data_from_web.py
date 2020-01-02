#!/usr/bin/env python3

import urllib.request
import mysql.connector

url = "http://tomas-balicek.cz/brodak/core/run.php"

db_user = "admin"
db_pwd = "1234"
db_name = "votuzilec"
db_table = "teplomer"
db_t1 = "t1"
db_t2 = "t2"
db_stamp = "stamp"


def get_data():

    fp = urllib.request.urlopen(url)
    fc = fp.read()
    fp.close()

    p = fc.find(b'<table>')
    p += len(b'<table>')
    table = fc[p:]
    p = table.find(b'</table>')
    table = table[0:p]

    data = []

    while True:
        b = table.find(b'<tr><td>')
        if b == -1:
            break
        b += len(b'<tr><td>')
        table = table[b:]
        e = table.find(b'</td></tr>')
        if e == -1:
            break
        row = table[:e].decode('utf8')

        data.append(row.split('</td><td>'))

    return data


def add_to_table(data):

    db = mysql.connector.connect(host="localhost", user=db_user, passwd=db_pwd, database=db_name)
    cursor = db.cursor()

    query = "CREATE TABLE IF NOT EXISTS {} (id INT AUTO_INCREMENT PRIMARY KEY, {} FLOAT, {} FLOAT, \
    {} TIMESTAMP DEFAULT CURRENT_TIMESTAMP)".format(db_table, db_t1, db_t2, db_stamp)
    cursor.execute(query)

    new_cnt = 0

    select_query = "SELECT * FROM {} WHERE {}".format(db_table, db_stamp) + " ='{}';"
    insert_query = 'INSERT INTO {} ({}, {}, {}) VALUES ('.format(db_table, db_t1, db_t2, db_stamp) + '{}, {}, "{}");'
    for d in data:
        cursor.execute(select_query.format(str(d[0])))
        if len(cursor.fetchall()) == 0:
            cursor.execute(insert_query.format(d[1], d[2], d[0]))
            new_cnt += 1
            print(d[0], d[1], d[2])

    db.commit()
    cursor.close()
    db.close()

    return new_cnt


def db_dump():

    db = mysql.connector.connect(host="localhost", user="admin", passwd="1234", database='votuzilec')
    cursor = db.cursor()

    query = "SELECT * FROM teplomer;"
    cursor.execute(query)

    for d in cursor.fetchall():
        print(d)

    cursor.close()
    db.close()


if __name__ == "__main__":

    data = get_data()
    #for d in data:
    #    print(d)
    new_cnt = add_to_table(data)
    print("{} new rows".format(new_cnt))
