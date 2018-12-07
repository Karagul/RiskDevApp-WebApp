import csv
import math
import pandas as pd 
import numpy as np
      

def loadCSV(csvfile):  
    with open(csvfile) as f:
        reader = csv.reader(f) 
        # next(reader) # skip header
        data = [r for r in reader]
        # for i in range(0,len(data)):
        #     print(data[i])
        return data

def monthToDay(month): # Convert month to day 30 or 31 day
    month = int(month)
    if month == 1 or month == 3 or month == 5 or month == 7 or month == 8 or month == 10 or month == 12:
        return 31
    elif month == 4 or month == 6 or month == 9 or month == 11:
        return 30
    elif month == 2:
        return 28

def dateDistance(startDate,dstDate): # Find distance between two dates
    stMonth = int(startDate[1])
    dstMonth = int(dstDate[1])

    if int(dstDate[0]) - int(startDate[0]) == 1:
        d1 = monthToDay(stMonth) - int(startDate[2]) # distance from start day to the end of the month
        d2 = 0 # distance between start month to December 31               
        d3 = int(dstDate[2]) # distance between January 1 to destination day

        for i in range(stMonth+1 , 13):
            d2 = d2 + monthToDay(i)  

        for i in range(1 , dstMonth):
            d3 = d3 + monthToDay(i)   

        # print('d1: {0}   d2: {1}  d3: {2}'.format(d1,d2,d3))
        return d1 + d2 + d3 + 1

    elif int(dstDate[0]) - int(startDate[0]) == 0:
        if stMonth != dstMonth:
            d1 = monthToDay(stMonth) - int(startDate[2]) # distance from start point to the end of the month
            d2 = 0 # distance between month                 

            for i in range(stMonth+1 , dstMonth):
                d2 = d2 + monthToDay(i)

            return d1 + d2 + int(dstDate[2]) + 1
        else:
            return abs(int(dstDate[2]) - int(startDate[2])) + 1 

    else:
        d1 = monthToDay(stMonth) - int(startDate[2]) # distance from start day to the end of the month
        d2 = 0 # distance between start month to December 31               
        d3 = int(dstDate[2])  # distance between January 1 to destination day
        d4 = (int(dstDate[0]) - int(startDate[0]) -1 ) * 365

        for i in range(stMonth+1 , 13):
            d2 = d2 + monthToDay(i)  

        for i in range(1 , dstMonth):
            d3 = d3 + monthToDay(i)   

        # print('d1: {0}   d2: {1}  d3: {2}  d4: {3}'.format(d1,d2,d3,d4))
        return d1 + d2 + d3 + d4 + 1
   
def weekNumber(dateDistance,dayRangeCnt):
    x = dateDistance/dayRangeCnt

    return math.ceil(x)
     
def weekGrouping(startDate,dayRangeCnt,dmy):
       
    total = len (dmy)
    x = 0
    while x < total:        
        if int(dmy[x][0]) < startDate[0]: # previous year
            dmy[x].append('0')     
        elif int(dmy[x][0]) == startDate[0]: # same year
            if int(dmy[x][1]) < startDate[1]: # same year and previous month
                dmy[x].append('0') 
            elif int(dmy[x][1]) == startDate[1]: # same month
                if int(dmy[x][2]) == startDate[2]: # same month and day
                    dmy[x].append('1') 
                elif int(dmy[x][2]) < startDate[2]: # same month and previous day
                    dmy[x].append('0')
                else: # same month but not same day
                    dmy[x].append(str(weekNumber(dateDistance(startDate,dmy[x]),dayRangeCnt)))
            else: # same year and next month
                 dmy[x].append(str(weekNumber(dateDistance(startDate,dmy[x]),dayRangeCnt)))
        else: # next year
            dmy[x].append(str(weekNumber(dateDistance(startDate,dmy[x]),dayRangeCnt)))

        print('Find week number for each movement: {0} of 100 %'.format(int( (x + 1)/total *100)),end='\r')       
        
        x = x +1   

def main(year, month, day):

    startDate = [year,month,day]                                                # [year, month, day]    
    print('Start date: {0}'.format(startDate))  


    emoveData = loadCSV("DataMart_Emove.csv")

    for i in range (len(emoveData)-1,0,-1):
        if emoveData[i][0] == '':
            emoveData.pop(i)
        else:
            break

    # print('emovement table length = ' + str(len(emoveData)))
    

    cnt1 = 1
    dmy = []
    while cnt1 != len(emoveData):
        # print(emoveData[cnt])
        tmp = []
        tmp.append(emoveData[cnt1][3])
        tmp.append(emoveData[cnt1][4])
        tmp.append(emoveData[cnt1][5])
        tmp.append(emoveData[cnt1][1])
        dmy.append(tmp)    
        cnt1 = cnt1 +1

    # print(dmy)    

    weekGrouping(startDate,7,dmy)

    print('Find week number for each movement: DONE        ')


    cnt2 = 1
    final = []
    finalW = 0
    while cnt2 != len(emoveData):
        tmp = []
        tmp.append(emoveData[cnt2][0])
        tmp.append(emoveData[cnt2][1])
        tmp.append(emoveData[cnt2][2])
        tmp.append(dmy[cnt2-1][4])
        final.append(tmp)  
        
        if finalW < int(final[cnt2-1][3]):
            finalW = int(final[cnt2-1][3])          
        cnt2 = cnt2 +1

    print('Final week: {0}'.format(finalW))


    header = ['LOC_CODE_ST_SubDistrictCode', 'LOC_CODE_END_SubDistrictCode', 'ANI_AMT', 'WEEK'] 

    # Write each movement in n week in to file number n
    for i in range(1,finalW+1):
        tmp = []    
        
        for j in range(0,len(final)):
            if final[j][3] == str(i):
                final[j][2] = int(final[j][2])
                tmp.append(final[j])            
            
            
        if len(tmp) > 1: 
            # Delete the week number that already chose
            for value in final[:]:
                if value[3] == str(i):
                    final.remove(value)

            #Sum all same move ANI_AMT
            df = pd.DataFrame.from_records(tmp, columns=header)       
            data = df.pivot_table(index='LOC_CODE_ST_SubDistrictCode',columns='LOC_CODE_END_SubDistrictCode',values='ANI_AMT',aggfunc='sum')
            
            data.fillna(0, inplace=True)
            df = df[(df.T != 0).any()]
            df = df.T[(df != 0).any()].T               
            
            data.to_csv('Weeks/week_' + str(i) + '.csv')
        
        print('Write each week movement into file: {0} of 100 %'.format(int(i/finalW * 100)),end='\r')  

    print('Write each week movement into file: DONE        ') 
   

if __name__ == "__main__": 
    import argparse

    parser = argparse.ArgumentParser(description='Process the sorting emovement week')
    parser.add_argument('Year', metavar='Y',type=int, 
                        help='emovement target year')
    parser.add_argument('Month', metavar='M',type=int, 
                        help='emovement target month')
    parser.add_argument('Day', metavar='D',type=int, 
                        help='emovement target day')
                        
    args = parser.parse_args()
    print(args.Year,args.Month,args.Day)
    main(year=args.Year, month=args.Month, day=args.Day)    
    