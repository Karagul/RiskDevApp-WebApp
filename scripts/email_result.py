# Parsing the population file
import io
import pandas as pd
import numpy as np
import pyodbc
import sys
import xlsxwriter

# Global variables
animal_master_dictionary = {}
animal_population_dictionary = {}
subdistrict_info_dictionary = {}

# Auxiliary functions
def translate_normdist_desc(desc):
    if desc[0] == "5":
        return "สูงมาก"
    elif desc[0] == "4":
        return "สูง"
    elif desc[0] == "3":
        return "ปานกลาง"
    elif desc[0] == "2":
        return "ต่ำ"
    else:
        return "N/A"

def get_subdistrict_info_string(subdistrict_code, db_connection):
    if subdistrict_code in subdistrict_info_dictionary:
        return subdistrict_info_dictionary[subdistrict_code]
    else:
        subdistrict_info_query = """
            SELECT province_name_th, district_name_th, subdistrict_name_th
              FROM subdistrict_master
              JOIN district_master ON subdistrict_master.district_code = district_master.district_code
              JOIN province_master ON district_master.province_code = province_master.province_code
             WHERE subdistrict_code = '{subdistrict_code}'
        """.format(subdistrict_code = subdistrict_code)
    
        subdistrict_info_result = pd.read_sql(subdistrict_info_query, db_connection)
        subdistrict_info_string = "จ.{province_name} อ.{district_name} ต.{subdistrict_name}".format(
            province_name = subdistrict_info_result.iloc[0]["province_name_th"],
            district_name = subdistrict_info_result.iloc[0]["district_name_th"].replace("กิ่งตำบล", ""),
            subdistrict_name = subdistrict_info_result.iloc[0]["subdistrict_name_th"]
        )
        subdistrict_info_dictionary[subdistrict_code] = subdistrict_info_string

        return subdistrict_info_string

def file_process_population(filepath, result_type, result_year, subdistrict_list_string):
    # Initializing the database handler
    try:
        #db_connection = pymysql.connect(host="localhost", user="riskdevapp", password="riskdevapp", db="riskdevapp", charset="utf8mb4", cursorclass=pymysql.cursors.DictCursor)
        db_connection = pyodbc.connect('DRIVER={ODBC Driver 17 for SQL Server};SERVER=localhost;DATABASE=riskdevapp;UID=riskdevapp;PWD=riskdevapp2018')
    except:
        print("ERR-CONNECTION")
        exit

    try:
        # Parsing the animal master dictionary
        animal_master_query = "SELECT animal_code, animal_name FROM animal_master"
        animal_master_dictionary = pd.read_sql(animal_master_query, db_connection, index_col='animal_code').to_dict()

        # Parsing the animal population file
        animal_population_dataframe = pd.read_csv(filepath, usecols=["SubDistrictCode", "ANIMAL_CODE", "AnimalTotal"], dtype={"SubDistrictCode": str, "ANIMAL_CODE": str, "AnimalTotal": str})
        animal_population_dataframe.columns = ["subdistrict_code", "animal_type", "animal_population"]
        animal_population_dataframe["animal_type"] = animal_population_dataframe.apply(lambda row: animal_master_dictionary["animal_name"][row["animal_type"]], axis=1)

        resulting_subdistrict_query = """
            SELECT subdistrict_code, province_name_th, district_name_th, subdistrict_name_th, 
                   risk_level_final, risk_level_normdist, starting_subdistrict_code AS starting_subdistrict
              FROM execute_result
              JOIN subdistrict_master ON execute_result.resulting_subdistrict_code = subdistrict_master.subdistrict_code
              JOIN district_master ON subdistrict_master.district_code = district_master.district_code
              JOIN province_master ON district_master.province_code = province_master.province_code
             WHERE execute_type_name = '{execute_type_name}'
               AND result_for_year = '{result_for_year}'
               AND starting_subdistrict_code IN ('""".format(
                   execute_type_name = result_type,
                   result_for_year = result_year
               ) + subdistrict_list_string.replace("-", "\',\'") + """')
             ORDER BY province_name_th, district_name_th, subdistrict_name_th, risk_level_final DESC         
        """

        resulting_subdistrict_dataframe = pd.read_sql(resulting_subdistrict_query, db_connection)
        # Parsing the starting subdistrict information and risk level description
        resulting_subdistrict_dataframe["starting_subdistrict"] = resulting_subdistrict_dataframe.apply(lambda row: get_subdistrict_info_string(row["starting_subdistrict"], db_connection), axis=1)
        resulting_subdistrict_dataframe["risk_level_normdist"]  = resulting_subdistrict_dataframe.apply(lambda row: translate_normdist_desc(row["risk_level_normdist"]), axis=1)

        # Appending animal population information
        resulting_subdistrict_dataframe = pd.merge(resulting_subdistrict_dataframe, animal_population_dataframe, on="subdistrict_code", how="left")
        
        # Preparing the output file
        file_output_dataframe = resulting_subdistrict_dataframe.sort_values(["province_name_th", "district_name_th", "subdistrict_name_th", "risk_level_final", "animal_population"], ascending=[True, True, True, False, False])
        file_output_dataframe.columns = ["รหัสตำบล", "จังหวัด", "อำเภอ", "ตำบล", "ค่าความเสี่ยง", "ระดับความเสี่ยง", "ตำบลเริ่มต้น", "ประเภทสัตว์", "จำนวนสัตว์ในตำบล"]

        # Parsing the selected data into CSV output buffer
        with io.BytesIO() as buffer:
            excel_writer = pd.ExcelWriter(buffer)
            file_output_dataframe[["จังหวัด", "อำเภอ", "ตำบล", "ค่าความเสี่ยง", "ระดับความเสี่ยง", "ประเภทสัตว์", "จำนวนสัตว์ในตำบล", "ตำบลเริ่มต้น"]].to_excel(excel_writer, index=False, encoding="utf8", merge_cells=True)
            excel_writer.save()
            return buffer.getvalue()
    except:
        print("ERR-INPUTFILE AT LINE [{lineno}]".format(lineno = sys.exc_info()[2].tb_lineno))
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
    email_port          = 465 # With SSL enabled
    email_password      = "riskdevapp2018"
    email_server        = "smtp.gmail.com"
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
        print("OK")
    except Exception:
        #print("ERR-EMAIL AT LINE [{lineno}]".format(lineno = sys.exc_info()[2].tb_lineno))
        print(sys.exc_info()[1])

if __name__ == "__main__":
    import argparse

    parser = argparse.ArgumentParser(description="สคริปอัตโนมัติสำหรับส่งผลการวิเคราะห์ความเสี่ยงฯ ผ่านทางอีเมล")
    parser.add_argument("result_type", metavar="ประเภทการวิเคราะห์", type=str, help="\{ASF, FMD, HPAI, NIPAH\}")
    parser.add_argument("result_year", metavar="ผลการวิเคราะห์สำหรับปี", type=str, help="ปีในรูปแบบคริสต์ศักราช")
    parser.add_argument("email_recipient", metavar="", type=str, help="")
    parser.add_argument("subdistrict_list_filename", metavar="", type=str, help="")

    args = parser.parse_args()
    #beg+++eKS09.05.2019 Workaround for long subdistrict list selection(>1000 starting subdistricts)
    subdistrict_string = ""
    
    with open("../temp/" + args.subdistrict_list_filename) as file:
        current_line = file.read()
        subdistrict_string = subdistrict_string + current_line

    main(args.result_type, args.result_year, args.email_recipient, subdistrict_string)

    # Remove the temp file after use
    import os
    os.remove("../temp/" + args.subdistrict_list_filename)
    #end+++eKS09.05.2019 Workaround for long subdistrict list selection(>1000 starting subdistricts)
