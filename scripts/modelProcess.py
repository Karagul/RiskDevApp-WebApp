import csv
import math
import pandas as pd 
import numpy as np
from os import listdir
from os.path import isfile, join
from pathlib import Path
import subprocess



def loadCSV(csvfile):  
    with open(csvfile) as f:
        reader = csv.reader(f)         
        data = [r for r in reader]        
        return data


def shortenEmoveTable(emoveTable,srcSDTable): # (emovement table  ,  an initial SD node or a list of SD node)
    print('Shorten emovement table: Start.  ',end='\r')

    emoveT = emoveTable.copy()
    # remove unnecessary start SD that not in the srcSDTable
    if type(srcSDTable) is list :
        for src1 in emoveTable[:]:
            if src1[0] != 'LOC_CODE_ST_SubDistrictCode':
                for idx, src2 in enumerate(srcSDTable):
                    if src1[0] == src2[0] :
                        break
                    elif src1[0] != src2[0] and idx == (len(srcSDTable)-1):
                        emoveT.remove(src1)
    else:        
        for src1 in emoveTable[:]:
            if src1[0] != 'LOC_CODE_ST_SubDistrictCode':               
                if src1[0] != srcSDTable:
                    emoveT.remove(src1)                   
    
    if len(emoveT) > 1:
        print('Shorten emovement table: Start.. ',end='\r')
        # remove the dst SD that is no movement
        removeLoc = []
        for x in range(1,len(emoveT[0])):
            for y in range(1,len(emoveT)):                
                if int(float(emoveT[y][x])) != 0 :
                    break
                elif int(float(emoveT[y][x])) == 0 and y == (len(emoveT) - 1): 
                    removeLoc.append(x)  
        # print('len of removeLoc: {0}'.format(len(removeLoc))) 

        print('Shorten emovement table: Start...',end='\r')
        out = [] 
        for val in emoveT:       
            tmp = []        
            for i in range(0,len(val)):                                                
                if i not in removeLoc:
                    tmp.append(val[i])        
            out.append(tmp)  

        print('Shorten emovement table: Done    ',end='\n') 
        return out
    else:
        print('Shorten emovement table: Done    ',end='\n') 
        return 0

def getPigPOP (pigPop , sdCode): # (Pig population table ,  target sub-district code)  
    try:
        num = pigPop[str(sdCode)]
        if num is None or int(num) == 0 or num == 'NA':
            return 0
        else:
            return num
    except :
        return 0

def getInfectedPig (outPutCSV, sdCode):  # (Pig population table ,  target sub-district code)
    out = ''
    for sd in outPutCSV:
        if sd[0] == sdCode:
            out = sd[2]
            break
    return out

def updatePigPop (pigPop , sdCode , amtPigChange): # (Pig population table ,  target sub-district code , amount of pig changed) 
    try:
        num = pigPop[str(sdCode)]
        if num is None or int(num) == 0 or num == 'NA':
            pigPop[str(sdCode)] = str(0)
        else:
            x = int(num) + amtPigChange
            if x > 0 :
                pigPop[str(sdCode)] = str(x)
            else:
                pigPop[str(sdCode)] = str(0)
        print('Pig population updated!')
    except :
        print('Not have a pig population for {0}'.format(sdCode))
   
def getMovedPig (outPutCSV, sdCode):  # (Pig population table ,  target sub-district code)
    out = ''
    for sd in outPutCSV:
        if sd[0] == sdCode:
            out = sd[1]
            break
    return out

def createFirstInputCSV(emoveTable,pigPop,initialNode,weekNum,weekPath,inputFolder,outPutFolder): # (shorten emovement table  , Pig population, name of initial node  ,  a number of week, week Path , input files folder , output files folder)
    print('Writing input csv file: ' + str(initialNode) + '_' +  str(weekNum) + '_week.csv => Start',end='\n')
    noOfDes = len(emoveTable[0]) - 1
    
    try:
        nextW = loadCSV( weekPath + '/week_' + str(weekNum + 1) + '.csv')
    except :
        print('Not has csv file for week number {0}'.format(weekNum+1))

    if nextW != None:

        inputPath  = inputFolder + '/'+ str(initialNode) + '_' +  str(weekNum) + '_week.csv'
        outPutPath = outPutFolder + '/'+ str(initialNode) + '_' +  str(weekNum) + '_week.csv'


        with open(inputPath, 'w', newline='') as csvfile:
            writer = csv.writer(csvfile, delimiter=',',quotechar='|', quoting=csv.QUOTE_MINIMAL)
            writer.writerow([str(noOfDes)])                                            # No. of cases (no. of destination)
     
            for i in range(1,noOfDes+1):   
                noOfsrc = 0
                src = []                
                for j in range(1,len(emoveTable)) :    
                    if int(float(emoveTable[j][i])) > 0:
                        noOfsrc = noOfsrc  + 1
                        src.append(j)

                # Get No. of dest. of next week
                destNxtW = []
                exportPig = []
                numOfdestNxtW = 0
                
                for sd in nextW:                    
                    if sd[0] != 'LOC_CODE_ST_SubDistrictCode':                       
                        if sd[0] == emoveTable[0][i]:                            
                            for n in range(1,len(sd)):
                                if int(float(sd[n])) > 0:
                                    numOfdestNxtW = numOfdestNxtW + 1
                                    destNxtW.append(sd[0]) 
                                    exportPig.append(sd[n])  
                                    break                     
                            break
                if numOfdestNxtW == 0: 
                    print('No destination for next week \nThis node is done')
                    return None, None

                writer.writerow([str(emoveTable[0][i])] +                              # Case sub-district ID
                                [str(noOfsrc)]      +                                  # no. of sourcse
                                [str(numOfdestNxtW)]          +                        # No. of destination (next week)
                                [getPigPOP(pigPop,emoveTable[0][i])])                  # Pig population in this node

                pigmoved = 0

                for k in range(0,len(src)):
                    writer.writerow([str(emoveTable[src[k]][0])] +                     # Sub-District_ID_Source                               
                                    [str(int(float(str(emoveTable[src[k]][i]))))] +    # no.of_moved_animal
                                    [str(1)])                                          # no.of_moved_infected_animal(1 for initial node)

                    updatePigPop(pigPop,str(emoveTable[src[k]][0]),                    # Update SD source's pig population
                                -int(float(str(emoveTable[src[k]][i]))))

                    pigmoved = pigmoved + int(float(str(emoveTable[src[k]][i])))       # Total pig moved in for Case sub-district ID

                updatePigPop(pigPop,str(emoveTable[0][i]),pigmoved)                    # Update Case sub-district ID's pig population
                                

                for p in range(0,len(destNxtW)):
                    writer.writerow([destNxtW[p]]    +                                 # Destination sub-district ID (next week)
                                    [exportPig[p]])                                    # Exported pigs (next week)
        
        print('Writing input csv file: ' + str(initialNode) + '_' +  str(weekNum) + '_week.csv => DONE   ')   

        return inputPath,outPutPath
    else:
        print('Not has csv file for week number {0}'.format(weekNum+1))
        return None, None
                              
def createNInputCSV(emoveTable,pigPop,nodeFromOutput,initialNode,weekNum,weekPath,inputFolder,outPutFolder): # (shorten emovement table  , Pig population , potential node from previous output ,  name of initial node  ,  a number of week , input files folder , output files folder )
    print('Writing input csv file: ' + str(initialNode) + '_' +  str(weekNum) + '_week.csv => Start',end='\n')
    noOfDes = len(nodeFromOutput) 
       
    try:
        nextW = loadCSV( weekPath + '/week_' + str(weekNum + 1) + '.csv')
    except :
        print('Not has csv file for week number {0}'.format(weekNum+1))

    if nextW != None:
        inputPath  = inputFolder + '/'+ str(initialNode) + '_' +  str(weekNum) + '_week.csv'
        outPutPath = outPutFolder + '/'+ str(initialNode) + '_' +  str(weekNum) + '_week.csv'

        with open(inputPath, 'w', newline='') as csvfile:
            writer = csv.writer(csvfile, delimiter=',',quotechar='|', quoting=csv.QUOTE_MINIMAL)
            writer.writerow([str(noOfDes)])                                             # No. of cases (no. of destination)
            for i in range(1,noOfDes+1):   
                noOfsrc = 0
                src = []                
                for j in range(1,len(emoveTable)) :    
                    if int(float(emoveTable[j][i])) > 0:
                        noOfsrc = noOfsrc  + 1
                        src.append(j)

                # Get No. of dest. of next week
                destNxtW = []
                exportPig = []
                numOfdestNxtW = 0

                for sd in nextW:
                    if sd[0] != 'LOC_CODE_ST_SubDistrictCode':
                        if sd[0] == emoveTable[0][i] : 
                            for n in range(1,len(sd)):
                                if int(float(sd[n])) > 0:
                                    numOfdestNxtW = numOfdestNxtW + 1
                                    destNxtW.append(sd[0]) 
                                    exportPig.append(sd[n]) 
                                    break                     
                            break

                writer.writerow([str(emoveTable[0][i])] +                              # Case sub-district ID
                                [str(noOfsrc)]      +                                  # no. of sourcse
                                [str(numOfdestNxtW)]          +                        # No. of destination (next week)
                                [getPigPOP(pigPop,emoveTable[0][i])])                  # Pig population in this node

                pigmoved = 0

                for k in range(0,len(src)):
                    writer.writerow([str(emoveTable[src[k]][0])] +                     # Sub-District_ID_Source                                    
                                    [str(getMovedPig(nodeFromOutput,                   # no.of_moved_animal
                                                    str(emoveTable[src[k]][0])))]  +    
                                    [str(getInfectedPig(nodeFromOutput,                # no.of_moved_infected_animal
                                                    str(emoveTable[src[k]][0])))])      

                    updatePigPop(pigPop,str(emoveTable[src[k]][0]),                    # Update SD source's pig population
                                -int(str(getMovedPig(nodeFromOutput,                   
                                                    str(emoveTable[src[k]][0])))))

                    pigmoved = pigmoved + int(str(getMovedPig(nodeFromOutput,          # Total pig moved in for Case sub-district ID      
                                                    str(emoveTable[src[k]][0]))))       

                updatePigPop(pigPop,str(emoveTable[0][i]),pigmoved)                    # Update Case sub-district ID's pig population                  

                for p in range(0,len(destNxtW)):
                    writer.writerow([destNxtW[p]]    +                                 # Destination sub-district ID (next week)
                                    [exportPig[p]])                                    # Exported pigs (next week)
                            
        print('Writing input csv file: ' + str(initialNode) + '_' +  str(weekNum) + '_week.csv => DONE   ')   

        return inputPath,outPutPath
    else:
        print('Not has csv file for week number {0}'.format(weekNum+1))
        return None, None

def readOutputCSVandFilter(path,sdCleanUpTable,pigPop): # Read output csv file to table and filter out SD that already in clean up stage  
    outCSV = loadCSV(path)  

    outNode = []    
    outRisk = []
    nxtNodeNum = int(outCSV[0][0])
    # exportedNodeNum = 0
    curRow = 1
    
    # Record the potential next initial node
    for j in range(0,nxtNodeNum): 
        
        # tmp.append(outCSV[curRow][0])
        curRow = curRow + 1
       
        for i in range(curRow ,curRow + int(float(outCSV[curRow - 1][1]))):
            for key in list(sdCleanUpTable):  
                if outCSV[i][0] == str(key) or outCSV[i][2] == '0':      
                    break  
                tmp = []               
                tmp.append(outCSV[i][0])                            # Node(sub-district) ID
                tmp.append(outCSV[i][1])                            # No. of exported pigs    
                tmp.append(outCSV[i][2])                            # No. of exported infected pigs 
                outNode.append(tmp)
        curRow = curRow + int(float(outCSV[curRow-1][1]))
        

    curRow = 1

    # Calculation of risk level in every infected sub-district
    for j in range(0,nxtNodeNum):             
        curRow = curRow + 1       
        for i in range(curRow ,curRow + int(float(outCSV[curRow - 1][1]))):
            for key in list(sdCleanUpTable):  
                if outCSV[i][2] == '0':      
                    break 
                tmp = []                    
                tmp.append(outCSV[i][0])  
                if int(getPigPOP(pigPop,outCSV[i][0])) == 0:
                    tmp.append('0')  
                else:            
                    tmp.append( str(int(float(outCSV[i][2])) / int(getPigPOP(pigPop,outCSV[i][0]))) )
                outRisk.append(tmp)
        curRow = curRow + int(float(outCSV[curRow-1][1]))                 

    return outNode , outRisk

def cleanUpStage(sdCleanUpTable): # Do clean up stage
    for key in list(sdCleanUpTable):
        if sdCleanUpTable[key] > 0:
            sdCleanUpTable[key] = sdCleanUpTable[key] - 7
    for key in list(sdCleanUpTable):
        if sdCleanUpTable[key] == 0:
            del sdCleanUpTable[key]
    return sdCleanUpTable

def addTocleanUp(emoveTable,sdCleanUpTable,cleanUpPeriod, flags=0): # Add dst SD to clean up stage

    if flags == 0:
        for i in range(1,len(emoveTable[0])):
            if len(sdCleanUpTable) > 0:            
                if emoveTable[0][i] in sdCleanUpTable:
                    if 0 < sdCleanUpTable[emoveTable[0][i]] < cleanUpPeriod:
                        sdCleanUpTable[emoveTable[0][i]] = sdCleanUpTable[emoveTable[0][i]] + 7
                else:
                    sdCleanUpTable[emoveTable[0][i]] = 14
            else:
                sdCleanUpTable[emoveTable[0][i]] = 14
        return sdCleanUpTable
    else:
        for i in range(1,len(emoveTable)):
            if len(sdCleanUpTable) > 0:            
                if emoveTable[i][0] in sdCleanUpTable:
                    if 0 < sdCleanUpTable[emoveTable[i][0]] < cleanUpPeriod:
                        sdCleanUpTable[emoveTable[i][0]] = sdCleanUpTable[emoveTable[i][0]] + 7
                else:
                    sdCleanUpTable[emoveTable[i][0]] = 14
            else:
                sdCleanUpTable[emoveTable[i][0]] = 14
        return sdCleanUpTable

def recordSDriskLevel(sdRiskRecord,riskLevel): # Record every SD's risk level after model processing          
    sdRiskRecord.extend(riskLevel)
    return sdRiskRecord

def convertListToDict(inputList):  # Convert list to dictionary and filter the duplicate value
    out = {}
    for line in inputList:
        if line[0] in out:
            # append the new number to the existing array at this slot
            out[line[0]].append(line[1])
        else:
            # create a new array in this slot
            out[line[0]] = [line[1]]
    return out

def modelProcessing(inputCSVPath,outputCSVPath,sirModelPath, beta, gamma): # Run R model 
    print('Running R model => START')
    try:
        subprocess.call ("Rscript --vanilla {0}/epidemicModel.R {1} {2} {0} {3} {4}".format(sirModelPath,inputCSVPath,outputCSVPath,beta,gamma), shell=True)
        print('Running R model => DONE')
    except:
        print('Running R model => FAILED')      

def filterRisk(sdRiskRecord , flags): # Filter the SD's risk level based on flag
    out = {}

    print('Filter the risk based on flag')
    return out

def process(csvFiles,initSD,firstW,pigPop,weekPath,numOfInitSDtoStart,cleanUpPeriod,sirModelPath,beta,gamma,inputFolder,outPutFolder):

    if len(initSD) > numOfInitSDtoStart:       
        if cleanUpPeriod > 0:   # Normal case for clean up process            

            for iniSD in initSD:        
                sdRiskRecord = []
                sdForCleanUp = {}
                inputCSVPath = ''
                outputCSVPath = ''
               

                # FIRST WEEK
                emoveShorten = shortenEmoveTable(firstW,iniSD)                                                             # Create first week shorten emovement  
                if emoveShorten is 0: 
                    print('Not have potential starter node.')
                    break
                inputCSVPath,outputCSVPath = createFirstInputCSV(emoveShorten,pigPop,iniSD,1,weekPath
                                                                 ,inputFolder,outPutFolder)                                # Create a path of the input csv file
                
                if inputCSVPath != None and outputCSVPath != None:                                                         # Check whether input file is complete to process                                      
                
                    sdForCleanUp = addTocleanUp(emoveShorten,sdForCleanUp,cleanUpPeriod,0)                                 # Add new dst SD to clean up stage or add more week to exsiting SD      
                    
                    modelProcessing(inputCSVPath,outputCSVPath,sirModelPath,beta,gamma)                                    # Run model 
                    
                    outputFile = Path(outputCSVPath)            
                    if outputFile.is_file() == False:                                                                      # Check whether output csv file is exist or not
                        print('Cannot find a output csv file from: ' + outputCSVPath)
                        break
                    
                    # NEXT OTHER WEEK
                    for week in range(2,len(csvFiles) + 1):  # len(csvFiles) + 1
                        cvFile = 'week_' + str(week) + '.csv'
                        nextW = ''
                        for cv in csvFiles[:]:
                            if cv == cvFile:
                                nextW = loadCSV(weekPath + '/' + cvFile)
                                break
                        if nextW != '':                       
                            sdForCleanUp = cleanUpStage(sdForCleanUp)                                                      # Clean up stage
                            outputFile = Path(outputCSVPath)            
                            if outputFile.is_file() == False:                                                              # Check whether output csv file is exist or not
                                print('Cannot find a output csv file from: ' + outputCSVPath)
                                break
                                            
                            node , riskLevel = readOutputCSVandFilter(outputCSVPath,sdForCleanUp,pigPop)                   # Get dst SD, risk level and infected animal number from output file and filter with cleanup stage
                            sdRiskRecord = recordSDriskLevel(sdRiskRecord,riskLevel)                                       # Record every output SD's risk level
                            emoveShorten = shortenEmoveTable(nextW,node)                                                   # Create n week shorten emovement
                            if emoveShorten == 0: 
                                print('Not have potential starter node.')
                                break
                            inputCSVPath,outputCSVPath = createNInputCSV(emoveShorten,pigPop,node,iniSD,week,weekPath
                                                                        ,inputFolder,outPutFolder)                         # Create a path of the input csv file
                            
                            if inputCSVPath != None and outputCSVPath != None:                                                           
                                sdForCleanUp = addTocleanUp(emoveShorten,sdForCleanUp,cleanUpPeriod,1)                     # Add new dst SD to clean up stage or add more week to exsiting SD 
                                                    
                                modelProcessing(inputCSVPath,outputCSVPath,sirModelPath,beta,gamma)                        # Run new model 
                            
                        else:
                            print('No csv file for week number {0}.'.format(week))
                            sdForCleanUp = cleanUpStage(sdForCleanUp)                                                      # Clean up stage
                    
                    if len(sdRiskRecord) > 0 :
                        riskRecorded = {}                                                                                  # Convert list to dict and filter the duplicate one for recording 
                        riskRecorded = convertListToDict(sdRiskRecord)   
                    # print(riskRecorded)


            # with open(riskLvlFolderPath + '/dict.csv', 'w') as csv_file:                                                 # Record the risklevel
            #     writer = csv.writer(csv_file)
            #     for key, value in riskRecorded.items():
            #         writer.writerow([key] + [value[0]])


        else: # Special case for clean up process  (i.e. the infected SD cannot be use as a dst SD)  
            sdRiskHistory = []
            sdForCleanUp = []

            emoveShorten = shortenEmoveTable(firstW,initSD[0])  
            print(createFirstInputCSV(emoveShorten,pigPop,initSD[0],1,weekPath,inputFolder,outPutFolder))                  # return as a path of the input csv file

    else:
        print('The number of initial SD is less than the {0} .'.format(numOfInitSDtoStart))



def main(cleanUpPeriod=14 , gamma=0.1 , beta=0.5):
    # Parameter
    numOfInitSDtoStart = 1  # default number
    # cleanUpPeriod = 14  # 2 weeks
    # gamma = 0.1
    # beta = 0.5

    # Paths
    pigPopPath = 'PIG_POP.csv'
    weekPath = 'Weeks'
    initialNodePath = 'Initial Node Risk.csv'
    inputFolder = 'InputCSV'
    outPutFolder = 'OutputCSV'
    riskLvlFolderPath = 'D:/Works/RiskApp/SourceCodePython/RiskLevel'
    sirModelPath = 'R_model'


    csvFiles = [f for f in listdir(weekPath) if isfile(join(weekPath, f))]


    if len(csvFiles) > 0:
        for cf in csvFiles[:]:
            if cf == 'week_1.csv':
                firstW = loadCSV( weekPath + "/week_1.csv")
                print('week_1.csv: Checked!')            
                break
            else:
                print('Cannot find Week_1.csv file.')
    else:
        print('No file in this directory.')


    # Find initial start node intersect with initial node risk
    initNode = loadCSV(initialNodePath)

    initSD = []
    if len(initNode) > 0:
        print('Initial Node Risk.csv: Checked!')        
        if len(initNode) and len(firstW) != 0:
            for i in range(1,len(firstW)):
                for j in range(0,len(initNode)):
                    if firstW[i][0] == initNode[j][0]:               
                        for k in range(1,len(firstW[i])):                    
                            if int(float(firstW[i][k])) != 0 and firstW[0][k] != initNode[j][0] :                                               
                                initSD.append(initNode[j][0])
                                break
    else:
        print('Cannot find Initial Node Risk.csv.')

    print('The number of initial sub-district: {0}'.format(len(initSD)))

    # Read Pig population csv file
    pigReader = csv.reader(open(pigPopPath, 'r'))
    pigPop = dict(pigReader)


    process(csvFiles,initSD,firstW,pigPop,weekPath,numOfInitSDtoStart,cleanUpPeriod,sirModelPath,beta,gamma,inputFolder,outPutFolder)



if __name__ == "__main__": 
    import argparse

    parser = argparse.ArgumentParser(description='Process the risk map with R model')
    parser.add_argument('cleanUpPeriod', metavar='C',type=int, 
                        help='clean up period for cleaning the infected node in day format (e.g. 14)')
    parser.add_argument('beta', metavar='B',type=float, 
                        help='beta value (e.g. 0.1)')
    parser.add_argument('gamma', metavar='G',type=float, 
                        help='gamma value (e.g. 0.5)')
                        
    args = parser.parse_args()
    print(args.cleanUpPeriod,args.beta,args.gamma)
    main(cleanUpPeriod=args.cleanUpPeriod, beta=args.beta, gamma=args.gamma)    
    




