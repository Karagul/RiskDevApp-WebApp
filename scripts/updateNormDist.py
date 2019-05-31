#beg+++iKS03.02.2019 Update the results to the database
import csv
import datetime
import pandas as pd
#import pyodbc
import pymysql
from os import listdir

connection = pymysql.connect(host="localhost",
                             user="riskdevapp",
                             password="riskdevapp",
                             db="riskdevapp",
                             charset="utf8mb4",
                             cursorclass=pymysql.cursors.DictCursor)
#connection = pyodbc.connect('DRIVER={ODBC Driver 17 for SQL Server};SERVER=localhost;DATABASE=riskdevapp;UID=riskdevapp;PWD=riskdevapp2018')
try:
    current_result_year = "2017"
    for current_result_type in ["ASF", "FMD", "HPAI", "NIPAH"]:
        # Fetch all candidates
        selection_sql = """SELECT execute_type_name, result_for_year, starting_subdistrict_code, resulting_subdistrict_code,
                                  risk_level_final
                             FROM execute_result
                            WHERE execute_type_name = '{execute_type_name}' AND result_for_year = '{result_for_year}'""".format(
                                execute_type_name = current_result_type,
                                result_for_year = current_result_year
                            )
        selection_df = pd.read_sql(selection_sql, connection)

        risk_level_stats = selection_df["risk_level_final"].describe(percentiles=[.20, .40, .60, .80])
        normdist_20p = risk_level_stats["20%"]
        normdist_40p = risk_level_stats["40%"]
        normdist_60p = risk_level_stats["60%"]
        normdist_80p = risk_level_stats["80%"]

        with connection.cursor() as cursor:
            for row_index, row_data in selection_df.iterrows():
                # Classifying with the current risk level
                risk_normdist = 0
                if row_data["risk_level_final"] > normdist_80p:
                    risk_normdist = 5
                elif row_data["risk_level_final"] > normdist_60p:
                    risk_normdist = 4
                elif row_data["risk_level_final"] > normdist_40p:
                    risk_normdist = 3
                elif row_data["risk_level_final"] > normdist_20p:
                    risk_normdist = 2
                else:
                    risk_normdist = 1

                # Update to the database
                update_sql = """UPDATE execute_result 
                                   SET risk_level_normdist = {risk_level_normdist}
                                 WHERE execute_type_name = '{execute_type_name}'
                                   AND result_for_year = '{result_for_year}'
                                   AND starting_subdistrict_code = '{starting_subdistrict_code}'
                                   AND resulting_subdistrict_code = '{resulting_subdistrict_code}'""".format(
                                       risk_level_normdist = risk_normdist,
                                       execute_type_name = current_result_type,
                                       result_for_year = current_result_year,
                                       starting_subdistrict_code = row_data["starting_subdistrict_code"],
                                       resulting_subdistrict_code = row_data["resulting_subdistrict_code"]
                                   )
                cursor.execute()
            connection.commit()
except:
    print("Died")