import numpy as np
import pandas as pd
import pyodbc

#db_connection = pymysql.connect(host="localhost", user="riskdevapp", password="riskdevapp", db="riskdevapp", charset="utf8mb4", cursorclass=pymysql.cursors.DictCursor)
db_connection = pyodbc.connect('DRIVER={ODBC Driver 17 for SQL Server};SERVER=localhost;DATABASE=riskdevapp;UID=riskdevapp;PWD=riskdevapp2018')

with db_connection.cursor() as db_cursor:
    for current_result_type in ("ASF", "FMD", "HPAI", "NIPAH"):
        print("Current result type: {result_type}".format(
            result_type = current_result_type
        ))

        result_fetch_query = """\
                              SELECT execute_type_name, result_for_year, starting_subdistrict_code, resulting_subdistrict_code, risk_level_final
                                FROM execute_result
                               WHERE execute_type_name = '{execute_type_name}' 
                                 AND result_for_year = '2017';""".format(
                                     execute_type_name = current_result_type
                                 )
        #db_cursor.execute(result_fetch_query)
        #execute_result = pd.DataFrame(db_cursor.fetchall())
        execute_result = pd.read_sql(result_fetch_query, db_connection)

        result_describe = execute_result["risk_level_final"].describe()
        result_mean  = result_describe["mean"]
        result_25q   = result_describe["25%"]
        result_75q   = result_describe["75%"]

        for row_index, row_data in execute_result.iterrows():
            risk_normdist = "N/A"

            if row_data["risk_level_final"] >= result_75q:
                risk_normdist = "5"
            elif row_data["risk_level_final"] >= result_mean:
                risk_normdist = "4"
            elif row_data["risk_level_final"] >= result_25q:
                risk_normdist = "3"
            else:
                risk_normdist = "2"

            update_query = """UPDATE execute_result 
                                     SET risk_level_normdist = '{risk_normdist}' 
                                   WHERE execute_type_name = '{execute_type_name}'
                                     AND result_for_year = '{result_for_year}'
                                     AND starting_subdistrict_code = '{starting_subdistrict_code}'
                                     AND resulting_subdistrict_code = '{resulting_subdistrict_code}'""".format(
                                         risk_normdist = risk_normdist,
                                         execute_type_name = row_data["execute_type_name"],
                                         result_for_year = row_data["result_for_year"],
                                         starting_subdistrict_code = row_data["starting_subdistrict_code"],
                                         resulting_subdistrict_code = row_data["resulting_subdistrict_code"]
                                     )
            db_cursor.execute(update_query)

        db_connection.commit()

        print("Processing for {result_type} is completed".format(
            result_type = current_result_type
        ))
