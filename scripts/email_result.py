# Parsing the population file
import io
import pandas as pd
import numpy as np
import pyodbc
import sys
import xlsxwriter

def translate_normdist_desc(desc):
    if desc[0] == "4":
        return "สูงมาก"
    elif desc[0] == "3":
        return "สูง"
    elif desc[0] == "2":
        return "ปานกลาง"
    elif desc[0] == "1":
        return "ต่ำ"
    else:
        return "N/A"

def file_process_population(filepath, result_type, result_year, subdistrict_list):
    # Initializing the database handler
    try:
        #db_connection = pymysql.connect(host="localhost", user="riskdevapp", password="riskdevapp", db="riskdevapp", charset="utf8mb4", cursorclass=pymysql.cursors.DictCursor)
        db_connection = pyodbc.connect('DRIVER={ODBC Driver 17 for SQL Server};SERVER=localhost;DATABASE=riskdevapp;UID=riskdevapp;PWD=riskdevapp2018')
    except:
        print("[ERROR] ไม่สามารถเชื่อมต่อกับระบบฐานข้อมูลได้ กรุณาติดต่อผู้ดูแลระบบ")
        exit

    try:
        # Parsing the animal population file
        subdistrict_list = subdistrict_list.split("-")
        file_input_dataframe = pd.read_csv(filepath, usecols=["SubDistrictCode", "ANIMAL_CODE", "AnimalTotal"], index_col=False)
        file_output_dataframe = file_input_dataframe[file_input_dataframe["SubDistrictCode"].isin(subdistrict_list)]
        
        # Creating the output dataframe
        file_output_dataframe["Province"]          = "PROVINCE"
        file_output_dataframe["District"]          = "DISTRICT"
        file_output_dataframe["Subdistrict"]       = "SUBDISTRICT"
        file_output_dataframe["RiskLevel"]         = "RISKLEVEL"
        file_output_dataframe["SourceSubdistrict"] = "SOURCESUBDISTRICT"
        file_output_dataframe["AnimalType"]        = "ANIMALTYPE"
        file_output_dataframe["RiskNormDist"]      = "N/A"

        # Populating the information
        with db_connection.cursor() as db_cursor:
            # Fetching animal master table
            animal_master_query = "SELECT animal_code, animal_name FROM animal_master;"
            #db_cursor.execute(animal_master_query)
            #animal_master_data = db_cursor.fetchall()
            animal_master_data = pd.read_sql(animal_master_query, db_connection)

            animal_master_table = dict()
            for row_index, row_data in animal_master_data.iterrows():
                animal_master_table[row_data["animal_code"]] = row_data["animal_name"]

            for row_index, row_data in file_output_dataframe.iterrows():
                # Query for resulting subdistrict information
                resulting_subdistrict_query = """\
                    SELECT province_name_th, district_name_th, subdistrict_name_th, risk_level_final, risk_level_normdist, starting_subdistrict_code
                      FROM execute_result
                      JOIN subdistrict_master ON execute_result.resulting_subdistrict_code = subdistrict_master.subdistrict_code
                      JOIN district_master ON subdistrict_master.district_code = district_master.district_code
                      JOIN province_master ON district_master.province_code = province_master.province_code
                     WHERE execute_type_name = '{result_type}'
                       AND result_for_year = '{result_year}'
                       AND resulting_subdistrict_code = '{subdistrict_code}'
                    """.format(
                        result_type = result_type,
                        result_year = result_year,
                        subdistrict_code = row_data["SubDistrictCode"]
                    )
                #db_cursor.execute(resulting_subdistrict_query)
                #query_result_ressub = db_cursor.fetchall()
                query_result_ressub = pd.read_sql(resulting_subdistrict_query, db_connection)
                file_output_dataframe.at[row_index, "Province"] = query_result_ressub.iloc[0]["province_name_th"]
                file_output_dataframe.at[row_index, "District"] = query_result_ressub.iloc[0]["district_name_th"].replace("กิ่งอำเภอ", "")
                file_output_dataframe.at[row_index, "Subdistrict"] = query_result_ressub.iloc[0]["subdistrict_name_th"]
                file_output_dataframe.at[row_index, "RiskLevel"] = query_result_ressub.iloc[0]["risk_level_final"]
                file_output_dataframe.at[row_index, "RiskNormDist"] = translate_normdist_desc(query_result_ressub.iloc[0]["risk_level_normdist"])
                file_output_dataframe.at[row_index, "SourceSubdistrict"] = query_result_ressub.iloc[0]["starting_subdistrict_code"]
                
                # Query for starting subdistrict information
                starting_subdistrict_query = """\
                    SELECT DISTINCT province_name_th, district_name_th, subdistrict_name_th
                      FROM subdistrict_master
                      JOIN district_master ON subdistrict_master.district_code = district_master.district_code
                      JOIN province_master ON district_master.province_code = province_master.province_code
                     WHERE subdistrict_code = '{subdistrict_code}'
                """.format(subdistrict_code = query_result_ressub.iloc[0]["starting_subdistrict_code"])
                #db_cursor.execute(starting_subdistrict_query)
                #query_result_stasub = db_cursor.fetchall()
                query_result_stasub = pd.read_sql(starting_subdistrict_query, db_connection)
                file_output_dataframe.at[row_index, "SourceSubdistrict"] = "จ.{province_name} อ.{district_name} ต.{subdistrict_name}".format(
                    province_name = query_result_stasub.iloc[0]["province_name_th"],
                    district_name = query_result_stasub.iloc[0]["district_name_th"].replace("กิ่งตำบล", ""),
                    subdistrict_name = query_result_stasub.iloc[0]["subdistrict_name_th"]
                )

                # Adding animal description
                file_output_dataframe.at[row_index, "AnimalType"] = animal_master_table[str(row_data["ANIMAL_CODE"])]

        # Change column name from EN to TH
        file_output_dataframe = file_output_dataframe.sort_values(["Province", "District", "Subdistrict", "RiskLevel", "RiskNormDist", "AnimalTotal"], ascending=[True, True, True, False, False, False])
        file_output_dataframe.columns = ["รหัสตำบล", "รหัสสัตว์", "จำนวนสัตว์ในตำบล", "จังหวัด", "อำเภอ", "ตำบล", "ค่าความเสี่ยง", "ระดับความเสี่ยง", "ตำบลเริ่มต้น", "ประเภทสัตว์"]        

        # Parsing the selected data into CSV output buffer
        with io.BytesIO() as buffer:
            #file_output_dataframe[["จังหวัด", "อำเภอ", "ตำบล", "ระดับความเสี่ยง", "จำนวนสัตว์ในตำบล", "ตำบลเริ่มต้น"]].to_csv(buffer, index=False, encoding="utf8")
            excel_writer = pd.ExcelWriter(buffer)
            file_output_dataframe[["จังหวัด", "อำเภอ", "ตำบล", "ค่าความเสี่ยง", "ระดับความเสี่ยง", "ประเภทสัตว์", "จำนวนสัตว์ในตำบล", "ตำบลเริ่มต้น"]].to_excel(excel_writer, index=False, encoding="utf8", merge_cells=True)
            excel_writer.save()
            return buffer.getvalue()
    except Exception as e:
        print("[ERROR] ไม่สามารถอ่านไฟล์ข้อมูลประชากรสัตว์ได้ กรุณาติดต่อผู้ดูแลระบบ")
        print(sys.exc_info()[0])
        print("At line [{lineno}]".format(lineno = sys.exc_info()[2].tb_lineno))
        exit

def main(result_type, result_year, email_recipient, subdistrict_list):
    from datetime import datetime

    if result_type == "ASF":
        file_input_location = "C:/WebApp/eSmart/RiskDevApp-WebApp/results/Population_nipah_" + result_year + ".csv"
    else:
        file_input_location = "C:/WebApp/eSmart/RiskDevApp-WebApp/results/Population_" + result_type + "_" + result_year + ".csv"
    email_attachment_file = file_process_population(file_input_location, result_type, result_year, subdistrict_list)
    email_attachment_name = "RESULT_{result_type}_{timestamp}.xlsx".format(
        result_type = result_type,
        timestamp = datetime.today().strftime('%Y%m%d')
    )

    # Sending an email
    from email.mime.application import MIMEApplication
    from email.mime.multipart import MIMEMultipart
    from email.mime.text import MIMEText
    import smtplib
    import ssl

    # Email settings
    email_from          = "dev.riskapp@gmail.com"
    email_port          = 587#465 # With SSL enabled
    email_password      = "riskdevapp2018"
    email_server        = "localhost"#"smtp.gmail.com"
    email_to            = email_recipient

    try:
        # Creating an email
        email_message_main  = MIMEMultipart()
        email_message_main["Subject"] = "[RISKAPP] ผลการวิเคราะห์ความเสี่ยงของโรค " + result_type
        email_message_main["From"]    = email_from
        email_message_main["To"]      = email_to

        # Email content: message
        email_message_html  = """\
        <html>
            <body>
                <h4>Requested data has been exported from the RiskApp system</h4>
                <p>Please see the attached file for further detail.</p>
                <p>Best regards,<br />RiskApp Team</p>
            </body>
        </html>
        """
        email_message_main.attach(MIMEText(email_message_html, "html"))

        # Email content: 
        #attachment_result_csv = MIMEApplication(email_attachment_file.encode("utf-8"))
        #attachment_result_csv["Content-Disposition"] = "attachment; filename={}".format(email_attachment_name)
        #email_message_main.attach(attachment_result_csv)
        attachment_result_excel = MIMEApplication(email_attachment_file)
        attachment_result_excel["Content-Disposition"] = "attachment; filename={}".format(email_attachment_name)
        email_message_main.attach(attachment_result_excel)

        email_context = ssl.create_default_context()
        with smtplib.SMTP_SSL(email_server, email_port, context=email_context) as smtp_instance:
            smtp_instance.login(email_from, email_password)
            smtp_instance.sendmail(email_message_main["From"], email_message_main["To"], email_message_main.as_string())
            smtp_instance.quit()
        print("ส่งอีเมลเรียบร้อยแล้ว")
    except Exception as e:
        print("ไม่สามารถส่งอีเมลได้ กรุณาติดต่อผู้ดูแลระบบ")
        print(sys.exc_info())
        print("At line [{lineno}]".format(lineno = sys.exc_info()[2].tb_lineno))

if __name__ == "__main__":
    import argparse

    parser = argparse.ArgumentParser(description="สคริปอัตโนมัติสำหรับส่งผลการวิเคราะห์ความเสี่ยงฯ ผ่านทางอีเมล")
    parser.add_argument("result_type", metavar="ประเภทการวิเคราะห์", type=str, help="\{ASF, FMD, HPAI, NIPAH\}")
    parser.add_argument("result_year", metavar="ผลการวิเคราะห์สำหรับปี", type=str, help="ปีในรูปแบบคริสต์ศักราช")
    parser.add_argument("email_recipient", metavar="", type=str, help="")
    parser.add_argument("subdistrict_list", metavar="", type=str, help="")

    #args = parser.parse_args()
    #main(args.result_type, args.result_year, args.email_recipient, args.subdistrict_list)
    main("FMD", "2017", "k.sutassananon@gmail.com", "160109-160201")
