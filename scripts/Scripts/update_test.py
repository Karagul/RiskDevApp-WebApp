#beg+++iKS03.02.2019 Update the results to the database
def doit(riskLvlFolderPath):
        import datetime
        import pyodbc
        from os import listdir
        import csv

        epidemicType = 'ASF'
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
                        print("Processing: " + file)
                        for row in currentFile:
                                try:
                                    insertQuery = "INSERT INTO execute_result VALUES('{epidemicType}', '2017', '{date}', '{user}', 'READY', '{sourceSubdistrict}', '{resultSubdistrict}', '{riskLevel}')".format(
                                        epidemicType = 'TEST',
                                        date = currentDate,
                                        user = "RiskDevApp",
                                        sourceSubdistrict = currentSubdistrict,
                                        resultSubdistrict = row[0],
                                        riskLevel = float(row[1])
                                    )
                                    cursor.execute(insertQuery)
                                except:
                                        continue
                connection.commit()
        finally:
                connection.close()
    #end+++iKS03.02.2019 Update the results to the database
	
doit("C:\\WebApp\\eSmart\\RiskDevApp-WebApp\\results\\RiskLevel");
