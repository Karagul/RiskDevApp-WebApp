import csv
import math
import pandas as pd 
import numpy as np

def loadCSV(csvfile , flagForheader):  # Read CSV file
    if flagForheader == 0:    # Not skip a header
        with open(csvfile) as f:
            reader = csv.reader(f)             
            data = [r for r in reader]           
            return data
    else:                      # Skip a header
        with open(csvfile) as f:
            reader = csv.reader(f) 
            next(reader) # skip header
            data = [r for r in reader]            
            return data

def checkMissPopInSD(popData , emoveData): # Check the sub-district that not have animal populaion 
    dupli = [x for x in emoveData for y in popData if int(y[0]) == int(x[0])]     

    if len(dupli) != len(popData) :
        notDupli = [x for x in emoveData  if x not in dupli[:]]        
        if len(notDupli) > 0:
            df = pd.DataFrame(notDupli,)
            df = df.drop_duplicates(subset=[0], keep='first')
            # print(df.iloc[:,[0]])        
            # df.to_csv('SourceCode/dataframe.csv', sep='\t')
            
            out = []
            for index, row in df.iterrows():
                # print (row[0])
                if len(out) > 0:
                    if str(row[0]) not in out[:]:
                        tmp = []
                        tmp.append(str(row[0]))
                        tmp.append(str(popData[0][1]))
                        tmp.append('0')
                        out.append(tmp)
                else:
                    tmp = []
                    tmp.append(str(row[0]))
                    tmp.append(str(popData[0][1]))
                    tmp.append('0')
                    out.append(tmp)                

            return out
        else:
            return None
    else:
        return None
    
def repairMissPopInSD(missedSD , popData): # Repair data in the SD that not have animal population
    for sd in missedSD:
        dCode = sd[0][:3]
        # print(dCode)

        cnt = 0
        num = 0
        for sd2 in popData:
            if dCode == sd2[0][:3]:
                cnt = cnt + 1
                num = num + int(sd2[2])
        if cnt != 0:
            sd[2] = str(int(num/cnt))
        else:
            print('This SD {0} not have information, manually add 0 for population.'.format(sd[0]))
            sd[2] = '0'

        # print(str(cnt) + ': ' + str(num))
    # print('Length of missed before add is ' + str(len(missedSD)))
    # print('Length of popdata before add is ' + str(len(popData)))
    for row in missedSD:
        popData.append(row)
    # print('Length of popdata after added is ' + str(len(popData)))

def main(emoveDataPath, popDataPath, savePath): # Main
    if emoveDataPath != None:
        emoveData = loadCSV(emoveDataPath, 1)                  # Read emovement csv file
    else:
        emoveData = loadCSV('E_Movement_HPAI_2017.csv', 1)                                   # Read emovement csv file
    if popDataPath != None:
        popData = loadCSV(popDataPath , 1)                # Read animal population csv file  
    else:    
        popData = loadCSV('Population_HPAI_2017.csv' , 1)                               # Read animal population csv file 

    print('Original animal population table length is ' + str(len(popData)) )

    missPOPsd = checkMissPopInSD(popData , emoveData)                                  # SD that not have animal populaton   

    print('Total of SD that have missing population is ' + str(len(missPOPsd)))
    if len(missPOPsd) != 0:
        repairMissPopInSD(missPOPsd,popData)                                           # Repair fiel if any animal population miss
        
        missPOPsd2 = checkMissPopInSD(popData , emoveData)                             # Check animal population in any SD again
        if missPOPsd2 != None :
            if len(missPOPsd2) == 0 :
                print('Finish repair missing animal population.\nFinal animal population table length is {0} '.format(len(popData))) 
            else:
                print('Still have missing animal population.\nConsidering run this process with the new saved CSV file again.')  
        else:
            print('Finish repair missing animal population.\nFinal animal population table length is {0} '.format(len(popData)))                          
    else:
        print('Not have any missing animal population')

      
    print('File => Saving...',end='\r')                                                # Save final CSV file
    csvFile = ''
    if savePath != None:
        csvFile = savePath + '/POULTRY_POP.csv'                                            # Read emovement csv file
    else:
        csvFile = 'POULTRY_POP.csv'                                                        # Read emovement csv file

    with open(csvFile, 'w', newline='') as myfile:
        wr = csv.writer(myfile, quoting=csv.QUOTE_ALL)
        for item in popData:        
            wr.writerow(item)
    print('File => Saved    ')

if __name__ == "__main__": 
    import argparse

    parser = argparse.ArgumentParser(description='Process the population data fixing')
    parser.add_argument('emovePath', metavar='emovement path',type=str,
                        help='the emovement path')
    parser.add_argument('popPath', metavar='population path',type=str,
                        help='the population data path')
    parser.add_argument('savePath', metavar='save path',type=str,
                        help='the path for save result')
                        
    args = parser.parse_args()
    print(args.emovePath,args.popPath,args.savePath)
    main(emoveDataPath=args.emovePath, popDataPath=args.popPath, savePath=args.savePath) 
    
    # python fixPop.py D:/Works/RiskApp/Git_Source_Code/E_Movement_nipah_2017.csv D:/Works/RiskApp/Git_Source_Code/Population_nipah_2017.csv D:/Works/RiskApp/Git_Source_Code   
    
    # python fixPop.py D:/RiskApp/SourceCode/E_Movement_nipah_2017.csv D:/RiskApp/SourceCode/Population_nipah_2017.csv D:/RiskApp/SourceCode   
    