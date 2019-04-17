import pandas as pd
import pymysql
import sys

try:
    # dataframe_animal_population = pd.read_csv("~/Repositories/RiskDevApp-WebApp/results/DataMart_AnimalPopulation_2017.csv", usecols=["ANIMAL_CODE", "ANI_TYPE_NAME"], encoding="ISO-8859-1")
    dataframe_animal_population = pd.read_csv("~/Repositories/RiskDevApp-WebApp/results/DataMart_AnimalPopulation_2017.csv", usecols=["ANIMAL_CODE", "ANI_TYPE_NAME"], encoding="utf8").drop_duplicates("ANIMAL_CODE")

    db_connection = pymysql.connect(host="localhost", user="riskdevapp", password="riskdevapp", db="riskdevapp", charset="utf8mb4", cursorclass=pymysql.cursors.DictCursor)

    with db_connection.cursor() as db_cursor:
        for row_index, row_data in dataframe_animal_population.iterrows():
            insert_query = "INSERT INTO animal_master VALUES('{animal_code}', '{animal_name}')".format(
                animal_code = row_data["ANIMAL_CODE"],
                animal_name = row_data["ANI_TYPE_NAME"]
            )
            db_cursor.execute(insert_query)
        db_connection.commit()
except:
    print(sys.exc_info()[0])