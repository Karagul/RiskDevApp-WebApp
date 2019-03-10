#beg+++iKS03.02.2019 Update the results to the database
import datetime
import pyodbc
from os import listdir

#connection = pymysql.connect(host="localhost",
#                             user="riskdevapp",
#                             password="riskdevapp",
#                             db="riskdevapp",
#                             charset="utf8mb4",
#                             cursorclass=pymysql.cursors.DictCursor)
connection = pyodbc.connect('DRIVER={ODBC Driver 17 for SQL Server};SERVER=localhost;DATABASE=riskdevapp;UID=riskdevapp;PWD=riskdevapp2018')
try:
    with connection.cursor() as cursor:
        currentDate = datetime.datetime.today().strftime("%Y-%m-%d")
        for file in listdir(riskLvlFolderPath):
            currentFile = csv.reader(open(riskLvlFolderPath + "/" + file, "r"))
            currentSubdistrict = file[0:6]
            for row in currentFile:
                insertQuery = "INSERT INTO execute_result VALUES('{epidemicType}', '2017', '{date}', '{user}', 'READY', '{sourceSubdistrict}', '{resultSubdistrict}', '{riskLevel}')".format(
                    epidemicType = epidemicType,
                    date = currentDate,
                    user = "RiskDevApp",
                    sourceSubdistrict = currentSubdistrict,
                    resultSubdistrict = row[0],
                    riskLevel = row[1]
                )
                cursor.execute(insertQuery)
    connection.commit()
finally:
    connection.close()
#end+++iKS03.02.2019 Update the results to the database