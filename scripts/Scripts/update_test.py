#beg+++iKS03.02.2019 Update the results to the database
    import datetime
    import pymysql
    from os import listdir

    connection = pymysql.connect(host="localhost",
                                 user="riskdevapp",
                                 password="riskdevapp",
                                 db="riskdevapp",
                                 charset="utf8mb4",
                                 cursorclass=pymysql.cursors.DictCursor)

    try:
        with connection.cursor() as cursor:
            currentDate = datetime.datetime.today().strftime("%Y-%m-%d")
            for file in listdir(riskLvlFolderPath):
                if file.endswith(".csv") and file[6] == ".":
                    currentFile = csv.reader(open(riskLvlFolderPath + "/" + file, "r"))
                    currentSubdistrict = file[0:6]
                    for row in currentFile:
                        insertQuery = "INSERT INTO result_nipah VALUES('NIPAH', '{date}', '{user}', 'READY', '{firstDate}', '{sourceSubdistrict}', '{resultSubdistrict}', '{riskLevel}')".format(
                            date = currentDate,
                            user = "RiskDevApp",
                            firstDate = "2017-01-01",
                            sourceSubdistrict = currentSubdistrict,
                            resultSubdistrict = row[0],
                            riskLevel = row[1]
                        )
                        cursor.execute(insertQuery)
        connection.commit()
    finally:
        connection.close()
    #end+++iKS03.02.2019 Update the results to the database