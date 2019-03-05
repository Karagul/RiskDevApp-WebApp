
def loadCSV(csvfile , flagForheader=0):  # Read CSV file
    import csv
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

def checkFileAndFolder(workingDirectory):  # Checking the required files and directories
    import sys
    # File
    pigPopPath = workingDirectory +'/CATTLE_POP.csv'
    initialNodePath = workingDirectory + '/FMD_SubdistrictRisk_High.csv'
    emovementPath = workingDirectory + '/E_Movement_FMD_2017.csv'

    # Folder
    weekPath =  workingDirectory + '/Weeks'    
    inputFolder = workingDirectory + '/InputCSV'
    outPutFolder = workingDirectory +'/OutputCSV'
    riskLvlFolderPath = workingDirectory + '/RiskLevel'
    sirModelPath = workingDirectory +'/R_model'

    # Checking status of required files
    # 1. Emovement file eg. EmoveData_nipah_2017.csv    
    try:
        file = open(emovementPath, 'r')
        print('{} is checked.'.format(emovementPath))  
    except IOError:
        print('Cannot find {}\nProgram will close.'.format(emovementPath))
        sys.exit(1)

    # 2. Initial node with high risk file eg. SubdistrictRisk_High.csv    
    try:
        file = open(initialNodePath, 'r')
        print('{} is checked.'.format(initialNodePath))
    except IOError:
        print('Cannot find {}\nProgram will close.'.format(initialNodePath))
        sys.exit(1)

    # 3. Animal population eg. PIG_POP.csv    
    try:
        file = open(pigPopPath, 'r')
        print('{} is checked.'.format(pigPopPath))
    except IOError:
        print('Cannot find {}\nProgram will close.'.format(pigPopPath))
        sys.exit(1)
         

    import os
    # Create required directories
    # 1. Week folder
    if not os.path.exists(weekPath):
        os.makedirs(weekPath)
        print(weekPath + ' is created.')
    # 1. Input file folder
    if not os.path.exists(inputFolder):
        os.makedirs(inputFolder)
        print(inputFolder + ' is created.')
    # 1. Output fle folder
    if not os.path.exists(outPutFolder):
        os.makedirs(outPutFolder)
        print(outPutFolder + ' is created.')
    # 1. Risk level folder
    if not os.path.exists(riskLvlFolderPath):
        os.makedirs(riskLvlFolderPath)
        print(riskLvlFolderPath + ' is created.')

    # Checking status of required R files
    # Simulating Emovement file eg. simulatingMovement.R    
    try:
        file = open(sirModelPath + '/FMD_simulatingMovement.R', 'r')
        print('{} is checked.'.format(sirModelPath + '/FMD_simulatingMovement.R'))  
    except IOError:
        print('Cannot find {}\nProgram will close.'.format(sirModelPath + '/FMD_simulatingMovement.R'))
        sys.exit(1)

    return True

def seir_modelProcessing(inputCSVPath,outputCSVPath,sirModelPath, beta, gamma, sigma): # Run SIR model 
    import subprocess
    print('Running R model => START')
    try:
        subprocess.call ("\"C:\\Program Files\\R\\R-3.5.1\\bin\\Rscript.exe\" --vanilla {0}/FMD_epidemicModel_190103.R {1} {2} {0} {3} {4} {5}".format(sirModelPath,inputCSVPath,outputCSVPath,beta,gamma,sigma), shell=True)
        print('Running R model => DONE')
    except:
        print('Running R model => FAILED')     

def simulateEmove_modelProcessing(sirModelPath,weekFolder,realEmoveCSVFile,initialSD, seed): # Run emove simulating 
    import subprocess
    print('Running R model => START')
    try:
        subprocess.call ("\"C:\\Program Files\\R\\R-3.5.1\\bin\\Rscript.exe\" --vanilla {0}/FMD_simulatingMovement.R {1} {2} {3} {4}".format(sirModelPath,weekFolder,realEmoveCSVFile,initialSD,seed), shell=True)
        print('Running R model => DONE')
    except:
        print('Running R model => FAILED') 

def sepEmoveToMatemove(similatedEmoveFile, weekNum): # Seperating simulated emovement file and return the corrected emovement format (i.e. the matrix way) for creating the input file
    import pandas as pd
    df = pd.read_csv(similatedEmoveFile,index_col=None)

    # Seperate only row that have current week         
    # dfW1 = df.loc[df['Week'] == weekNum]

    # Seperate only row that have next week  
    # dfW2 = df.loc[df['Week'] == (weekNum + 1)]

    # Slicing, to cut out the 'Week' column
    dfW1 = df.iloc[:,0:3]
    # dfW2 = dfW2.iloc[:,0:3]
           
    # Convert into matrix formation (aka pivot table) and fill missing with 0
    data1 = dfW1.pivot_table(index='Source_SubDistrictCode',columns='Destination_SubDistrictCode',values='ANI_AMT',aggfunc='sum')            
    data1.fillna(0, inplace=True)    

    # data2 = dfW2.pivot_table(index='Source_SubDistrictCode',columns='Destination_SubDistrictCode',values='ANI_AMT',aggfunc='sum')            
    # data2.fillna(0, inplace=True)
    
    return data1 

def getPigPOP (pigPop , sdCode): # (Pig population table ,  target sub-district code)  
    try:
        num = pigPop[str(sdCode)]
        if num is None or int(num) == 0 or num == 'NA':
            return 0
        else:
            return num
    except :
        return 0

def updatePigPop (pigPop , sdCode , amtPigChange): # (Pig population table ,  target sub-district code , amount of pig changed) 
    try:
        num = pigPop[str(sdCode)]
        if num is None or int(num) == 0 or num == 'NA':
            pigPop[str(sdCode)] = str(0)
        else:
            x = int(num) + int(amtPigChange)
            if x > 0 :
                pigPop[str(sdCode)] = str(x)
            else:
                pigPop[str(sdCode)] = str(0)
        print('Pig population at {} is updated!'.format(sdCode))
    except Exception as e:
        print(e)
        print('Cannot update a pig population for {0}'.format(sdCode))
   
def convertDFtoList(dataframe): # Convert dataframe to list
    
    # Get rows name
    index = list(dataframe.index)
    # Get columns name
    column = list(dataframe)
    # Get data 
    data = dataframe.values.tolist()

    # Insert rows name for each data'row
    for i in range(0,len(data)):
        data[i].insert(0,index[i])
        
    outList = []

    # Insert column names and put the columns into the list
    column.insert(0,0)    
    outList.append(column)

    # Put data into the list
    for dat in data:
        outList.append(dat)

    return outList
   
def shortenEmoveTable(emoveTable,srcSDTable): # Shorten the simulated files
    print('Shorten emovement table: Start.  ',end='\r')

    emoveT = emoveTable.copy()
    # remove unnecessary start SD that not in the srcSDTable
    if type(srcSDTable) is list :
        for src1 in emoveTable[:]:
            if src1[0] != 0:
                for idx, src2 in enumerate(srcSDTable):
                    if src1[0] == int(src2[0]) :
                        break
                    elif src1[0] != int(src2[0]) and idx == (len(srcSDTable)-1):
                        emoveT.remove(src1)
    else:        
        for src1 in emoveTable[:]:
            if src1[0] != 0:               
                if src1[0] != int(srcSDTable):                    
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

def createFirstInputCSV(curWeekEmove,nxtWeekEmove,pigPop,initialNode,weekNum,weekPath,inputFolder,outPutFolder):  # Create the first week input csv file
    import csv
    import os

    print('Writing input csv file: ' + str(initialNode) + '_' +  str(weekNum) + '_week.csv => Start',end='\n')
    noOfDes = len(curWeekEmove[0]) - 1
    noOfCase = noOfDes    
    print('Total destination: ' + str(noOfDes))

    inputPath  = inputFolder + '/'+ str(initialNode) + '_' +  str(weekNum) + '_week.csv'
    outPutPath = outPutFolder + '/'+ str(initialNode) + '_' +  str(weekNum) + '_week.csv'


    with open(inputPath, 'w', newline='') as csvfile:
        writer = csv.writer(csvfile, delimiter=',',quotechar='|', quoting=csv.QUOTE_MINIMAL)
        writer.writerow([str(noOfDes)])                                                # No. of cases (no. of destination)
     
        for i in range(1,noOfDes+1):   
                noOfsrc = 0
                src = []                
                for j in range(1,len(curWeekEmove)) :    
                    if int(curWeekEmove[j][i]) > 0:
                        noOfsrc = noOfsrc  + 1                        
                        src.append(j)
                
                # Get No. of dest. of next week
                destNxtW = []
                exportPig = []
                numOfdestNxtW = 0
                
                for sd in nxtWeekEmove:                    
                    if int(sd[0]) != 0:                       
                        if sd[0] == curWeekEmove[0][i]:                            
                            for n in range(1,len(sd)):
                                if int(sd[n]) > 0:
                                    numOfdestNxtW = numOfdestNxtW + 1
                                    destNxtW.append(nxtWeekEmove[0][n]) 
                                    exportPig.append(str(int(sd[n])))  
                                    break                     
                            break

                if numOfdestNxtW != 0:                                                     # Check need to have at least one dest. in next week
                   
                    writer.writerow([str(curWeekEmove[0][i])] +                            # Case sub-district ID
                                    [str(noOfsrc)]      +                                  # no. of sourcse
                                    [str(numOfdestNxtW)]          +                        # No. of destination (next week)
                                    [getPigPOP(pigPop,curWeekEmove[0][i])])                # Pig population in this node

                    pigmoved = 0

                    for k in range(0,len(src)):
                        writer.writerow([str(curWeekEmove[src[k]][0])] +                   # Sub-District_ID_Source                               
                                        [str(int(curWeekEmove[src[k]][i]))] +              # no.of_moved_animal
                                        [str(1)])                                          # no.of_moved_infected_animal(1 for initial node)

                        updatePigPop(pigPop,str(curWeekEmove[src[k]][0]),                  # Update SD source's pig population
                                    -int(curWeekEmove[src[k]][i]))

                        pigmoved = pigmoved + int(curWeekEmove[src[k]][i])                 # Total pig moved in for Case sub-district ID

                    updatePigPop(pigPop,str(curWeekEmove[0][i]),pigmoved)                  # Update Case sub-district ID's pig population
                                    

                    for p in range(0,len(destNxtW)):
                        writer.writerow([destNxtW[p]]    +                                 # Destination sub-district ID (next week)
                                        [exportPig[p]])                                    # Exported pigs (next week)
                else:
                    noOfCase = noOfCase - 1
        
    if noOfCase != noOfDes :                                                               # Replace the right no. of case and check whether is 0 or not
        if noOfCase != 0:
            tempData = []
            # Read all data from the csv file.
            with open(inputPath) as b:
                bottles = csv.reader(b)
                tempData.extend(bottles)
            tempData.remove(tempData[0])
            with open(inputPath, 'w', newline='') as b:
                writer = csv.writer(b)
                writer.writerow([str(noOfCase)])
                for row in tempData:                    
                    writer.writerow(row)
        else:
            print('The number of case is 0\nDelete the input file')
            # Delete file
            os.remove(inputPath)
            return 0,0      
    


    print('Writing input csv file: ' + str(initialNode) + '_' +  str(weekNum) + '_week.csv => DONE   ')   

    return inputPath,outPutPath    
  
def firstWeekInputCSV(curWeekEmove,pigPop,initialNode,weekNum,weekPath,inputFolder,outPutFolder):  # Create the first week input csv file
    import csv    

    print('Writing input csv file: ' + str(initialNode) + '_' +  str(weekNum) + '_week.csv => Start',end='\n')
    noOfCase = len(curWeekEmove) - 1
    print('Total case: ' + str(noOfCase))
    print('Total destination: ' + str(len(curWeekEmove[0])))

    inputPath  = inputFolder + '/'+ str(initialNode) + '_' +  str(weekNum) + '_week.csv'
    outPutPath = outPutFolder + '/'+ str(initialNode) + '_' +  str(weekNum) + '_week.csv'

    with open(inputPath, 'w', newline='') as csvfile:
        writer = csv.writer(csvfile, delimiter=',',quotechar='|', quoting=csv.QUOTE_MINIMAL)
        writer.writerow([str(noOfCase)])                                                       # No. of cases (no. of initial source)
        
        for i in range(1,noOfCase+1):
            noOfdst = 0  
            dst = [] 
            exportPig = [] 
            for j in range(1,len(curWeekEmove[0])) :                            
                if int(curWeekEmove[i][j]) > 0:
                    noOfdst = noOfdst  + 1                        
                    dst.append(int(curWeekEmove[0][j]))
                    exportPig.append(int(curWeekEmove[i][j]))


            # Find pig population of the case node
            pigPop_case = int(getPigPOP(pigPop,curWeekEmove[i][0]))   

            # Node case's pig population have to more than 0
            if pigPop_case > 0:         
                for j in range(0,len(dst)):
                    pigPop_case = pigPop_case + exportPig[j]
                # Combine with imported pigs (1 for first week)
                pigPop_case = pigPop_case + 1
                

                writer.writerow([curWeekEmove[i][0]] +                                         # Case sub-district ID
                                            [1]      +                                         # no. of sourcse (1 for first week)
                                            [noOfdst]          +                               # No. of destination
                                            [pigPop_case])                                     # Pig population in this node (have to combine with tranferd pig first)

                writer.writerow([999999] +                                                     # Source subdistrict ID (999999 for first week)
                                [1]      +                                                     # imported pigs (1 for first week)
                                [1]      +                                                     # imported infected pigs (1 for first week) (I)
                                [0])                                                           # imported exposed pigs (0 for first week)  (E)

                for j in range(0,len(dst)):
                    writer.writerow([dst[j]]    +                                              # Destination sub-district ID 
                                            [exportPig[j]])                                    # Exported pigs 
            else:
                return 0, 0
        
    
    print('Writing input csv file: ' + str(initialNode) + '_' +  str(weekNum) + '_week.csv => DONE   ')   

    return inputPath,outPutPath                                     

def cleanUpStage(sdCleanUpTable): # Do clean up stage
    for key in list(sdCleanUpTable):
        if sdCleanUpTable[key] > 0:
            sdCleanUpTable[key] = sdCleanUpTable[key] - 7
    for key in list(sdCleanUpTable):
        if sdCleanUpTable[key] == 0:
            del sdCleanUpTable[key]
    return sdCleanUpTable

def addTocleanUp(emoveTable,sdCleanUpTable,cleanUpPeriod, ): # Add dst SD to clean up stage

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
        
def readOutputCSVandFilter(path,sdCleanUpTable,pigPop): # Read output csv file to table and filter out SD that already in clean up stage  
    outCSV = loadCSV(path,0)  

    outNode = []    
    outRisk = []
    # nxtNodeNum = int(float(outCSV[0][0]))
    maxLine = len(outCSV)    
    curRow = 1
    
    while curRow < maxLine:
        # for cnt in range(0,nxtNodeNum):
            # Record the case nodes and their risk level
            tmpRisk = []
            tmpRisk.append(int(float(outCSV[curRow][0])))
            tmpRisk.append(float(outCSV[curRow][2]))
            outRisk.append(tmpRisk)

            sourceNode = outCSV[curRow][0]

            curRow = curRow + 1
            
            # Record the potential next initial node
            for i in range(curRow ,curRow + int(float(outCSV[curRow - 1][1]))):                
                tmp = []               
                tmp.append(outCSV[i][0])                            # Node(sub-district) ID
                tmp.append(outCSV[i][1])                            # No. of exported pigs    
                tmp.append(outCSV[i][2])                            # No. of exported infected pigs 
                tmp.append(outCSV[i][3])                            # No. of exported exposed pigs 
                tmp.append(sourceNode)                              # Source node                
                

                # Update pig population
                updatePigPop(pigPop,outCSV[i][0],outCSV[i][1])

                for key in list(sdCleanUpTable):  
                    if outCSV[i][0] == str(key) or outCSV[i][2] == '0':   
                        tmp = []   
                        break  
                
                if len(tmp) > 0:
                    outNode.append(tmp)

            curRow = curRow + int(float(outCSV[curRow-1][1]))


    if len(outNode) == 0:
        return outNode , outRisk, True
    else:
        return outNode , outRisk, False
    
def recordSDriskLevel(sdRiskRecord,riskLevel): # Record every SD's risk level after model processing          
    sdRiskRecord.extend(riskLevel)
    return sdRiskRecord

def nextNweekInitialSD(initialFromOutput,workingDirectory): # Create next N week's initial subdistrict from output file
    outPath = workingDirectory + '/SubdistrictNweek.csv'
    import csv

    with open(outPath, 'w', newline='') as csvfile:
        writer = csv.writer(csvfile, delimiter=',',quotechar='|', quoting=csv.QUOTE_MINIMAL)
        writer.writerow(['SD_CODE']) 
        for sd in initialFromOutput:            
            writer.writerow([str(sd[0])])    
    return outPath

def findSrcsAndPigExp(node, outputInitiNodeList): # Get the source nodes and exported pigs   
    source = []
    # print(outputInitiNodeList)
    for nd in outputInitiNodeList:
        if int(nd[0]) == int(node):  
            tmp = []                      
            tmp.append(nd[4]) # 3
            tmp.append(nd[1]) # 1
            tmp.append(nd[2]) # 2
            tmp.append(nd[3])
            source.append(tmp)
    return source

def nWeekInputCSV(curWeekEmove,pigPop,initialNodeList,originalInitlNode,weekNum,weekPath,inputFolder,outPutFolder):  # Create the first week input csv file
    import csv    

    print('Writing input csv file: ' + str(originalInitlNode) + '_' +  str(weekNum) + '_week.csv => Start',end='\n')
    noOfCase = len(curWeekEmove) - 1
    noOfCase_tmp = noOfCase
    print('Total case: ' + str(noOfCase))
    print('Total destination: ' + str(len(curWeekEmove[0])))

    inputPath  = inputFolder + '/'+ str(originalInitlNode) + '_' +  str(weekNum) + '_week.csv'
    outPutPath = outPutFolder + '/'+ str(originalInitlNode) + '_' +  str(weekNum) + '_week.csv'

    with open(inputPath, 'w', newline='') as csvfile:
        writer = csv.writer(csvfile, delimiter=',',quotechar='|', quoting=csv.QUOTE_MINIMAL)
        writer.writerow([str(noOfCase)])                                                       # No. of cases (no. of initial source)
        
        for i in range(1,noOfCase+1):
            noOfdst = 0  
            dst = [] 
            exportPig = [] 
            for j in range(1,len(curWeekEmove[0])) :                            
                if int(curWeekEmove[i][j]) > 0:
                    noOfdst = noOfdst  + 1                        
                    dst.append(int(curWeekEmove[0][j]))
                    exportPig.append(int(curWeekEmove[i][j]))


            # Find pig population of the case node
            pigPop_case = int(getPigPOP(pigPop,curWeekEmove[i][0]))   

            # Node case's pig population have to more than 0
            if pigPop_case > 0:         
                for j in range(0,len(dst)):
                    pigPop_case = pigPop_case + exportPig[j]
                # Combine with imported pigs (1 for first week)
                pigPop_case = pigPop_case + 1
                
                sourcesNode = findSrcsAndPigExp(curWeekEmove[i][0],initialNodeList)
                # print(sourcesNode)
                # print(len(sourcesNode))
                
                writer.writerow([curWeekEmove[i][0]] +                                         # Case sub-district ID
                                [len(sourcesNode)]+                                            # no. of sourcse 
                                [noOfdst]          +                                           # No. of destination
                                [pigPop_case])                                                 # Pig population in this node (have to combine with tranferd pig first)
                
                for k in range(0,len(sourcesNode)):
                    writer.writerow([sourcesNode[k][0]] +                                      # Source subdistrict ID 
                                    [sourcesNode[k][1]] +                                      # imported pigs 
                                    [sourcesNode[k][2]] +                                      # imported infected pigs 
                                    [sourcesNode[k][3]])                                       # imported exposed pigs  

                for j in range(0,len(dst)):
                    writer.writerow([dst[j]]    +                                              # Destination sub-district ID 
                                            [exportPig[j]])                                    # Exported pigs 
            else:
                print('{} has no pig population, remove from input file'.format(curWeekEmove[i][0]))
                noOfCase_tmp = noOfCase_tmp - 1
        
    if noOfCase != noOfCase_tmp:                                                            # Replace the right no. of case
        tempData = []
        # Read all data from the csv file.
        with open(inputPath) as b:
            bottles = csv.reader(b)
            tempData.extend(bottles)
            tempData.remove(tempData[0])
        with open(inputPath, 'w', newline='') as b:
            writer = csv.writer(b)
            writer.writerow([str(noOfCase_tmp)])
            for row in tempData:                    
                writer.writerow(row)

    print('Writing input csv file: ' + str(originalInitlNode) + '_' +  str(weekNum) + '_week.csv => DONE   ')   

    return inputPath,outPutPath                                     

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

def filterRisk(sdRiskRecord, riskLvlFolderPath, subdistrict, flags = 0): # Filter the SD's risk level based on flag    
    import csv
    with open(riskLvlFolderPath + '/' + subdistrict + '.csv', 'w', newline='') as csvfile:
        writer = csv.writer(csvfile, delimiter=',',quotechar='|', quoting=csv.QUOTE_MINIMAL)
                                                             # No. of cases (no. of initial source)
        if flags == 0: # Filter risk by its maximum
            for sd in sdRiskRecord:                
                maxRisk = sdRiskRecord[sd][0]
                for risk in sdRiskRecord[sd]:               
                    if maxRisk < risk:
                        maxRisk = risk
                print('The maximum risk of subdistrict {} is {}'.format(sd,maxRisk))
                writer.writerow([sd] + [maxRisk])  
        else: # Filter risk by its average
            for sd in sdRiskRecord:                
                totalRisk = sdRiskRecord[sd][0]
                for risk in sdRiskRecord[sd]:         
                    totalRisk = totalRisk + risk                
                print('The average risk of subdistrict {} is {}'.format(sd,totalRisk/len(sdRiskRecord[sd])))
                writer.writerow([sd] + [totalRisk/len(sdRiskRecord[sd])])  

    print('\nThe starter subdistrict {0} risk file is saved at {1}\n'.format(subdistrict,riskLvlFolderPath))
    return riskLvlFolderPath + '/' + subdistrict + '.csv'

def modelProcess(initSD,pigPop,weekPath,realEmoveFile,initialSDFile,cleanUpPeriod,sirModelPath,beta,gamma,sigma,inputFolder,outPutFolder,workingDirectory,riskLvlFolderPath,maxLoop): # Processing R model to create risk map 
    if cleanUpPeriod > 0:   # Normal case for clean up process
        print('Normal case')

        # Random seed number for simulating emovement
        import random
        seed = random.randrange(1000,9999)
        print('Random seed number: ' + str(seed))
        
        weekNum = 1  # Week number start with week 1        
        for iniSD in initSD: # Process through every initial SD
            print('Subdistrict: ' + str(iniSD[0] + ' process...'))

            sdRiskRecord = []
            sdForCleanUp = {}
            inputCSVPath = ''
            outputCSVPath = ''

            # Loop stoping flag
            stopFlag = False

            # >>>>>>>   FIRST WEEK   <<<<<<<<
            print('\n\nWeek number {} \n\n'.format(weekNum))

            # Simulate emove for : First Week
            simulateEmove_modelProcessing(sirModelPath,weekPath,realEmoveFile,initialSDFile,seed)
            
            curWeek  = sepEmoveToMatemove(weekPath + '/simMovement.csv', 1)
            curWeekList = convertDFtoList(curWeek)            

            curWeek.to_csv(weekPath + '/week_' + str(weekNum) + '.csv')
            

            emoveShorten = shortenEmoveTable(curWeekList,iniSD[0]) 
            # print(len(emoveShorten))
            if emoveShorten is not 0:                              
                inputCSVPath,outputCSVPath = firstWeekInputCSV(emoveShorten, pigPop, 
                                            iniSD[0], weekNum, weekPath, inputFolder, outPutFolder)
                
                if inputCSVPath != 0 and outputCSVPath != 0:       
                    sdForCleanUp = addTocleanUp(emoveShorten,sdForCleanUp,cleanUpPeriod)                                            # Add new dst SD to clean up stage or add more week to exsiting SD      
                    # print(sdForCleanUp)
                    seir_modelProcessing(inputCSVPath,outputCSVPath,sirModelPath,beta,gamma,sigma)                                  # Run SEIR model 
                    
                    from pathlib import Path
                    outputFile = Path(outputCSVPath)   

                    if outputFile.is_file() == True:                                                                                # Check whether output csv file is exist or not
                        # >>>>>>>  NEXT OTHER WEEK  <<<<<<<<
                        
                        # Read output 
                        node , riskLevel, stopFlag = readOutputCSVandFilter(outputCSVPath,sdForCleanUp,pigPop)                      # Get dst SD, risk level and infected animal number from output file and filter with cleanup stage
                        
                        sdRiskRecord = recordSDriskLevel(sdRiskRecord,riskLevel)                                                    # Record every output SD's risk level
                        # print(sdRiskRecord)

                        if stopFlag is False:                                                                                       # Stop flasg need to be false
                            loopEndNum = 0
                            loopEndMax = maxLoop                                                                                    # Maximum loop
                            while stopFlag is False and loopEndNum < loopEndMax:
                                
                                weekNum = weekNum + 1
                                print('\n\nWeek number {} \n\n'.format(weekNum))

                                initialSDforNweek = nextNweekInitialSD(node,workingDirectory)                                       # Create next initial node csv file for simulating emovement
                               
                                simulateEmove_modelProcessing(sirModelPath,weekPath,realEmoveFile,initialSDforNweek,seed)           # Simulate emove for : N Week
                                
                                curWeek  = sepEmoveToMatemove(weekPath + '/simMovement.csv', 1)
                                curWeekList = convertDFtoList(curWeek)
                                
                                curWeek.to_csv(weekPath + '/week_' + str(weekNum) + '.csv')
                                
                                # break
                                emoveShorten = shortenEmoveTable(curWeekList,node)
                                if emoveShorten is not 0:                              
                                    inputCSVPath,outputCSVPath = nWeekInputCSV(emoveShorten, pigPop, 
                                                                node,iniSD[0], weekNum, weekPath, inputFolder, outPutFolder)

                                    if len(loadCSV(inputCSVPath)) > 1:       
                                        sdForCleanUp = addTocleanUp(emoveShorten,sdForCleanUp,cleanUpPeriod)                        # Add new dst SD to clean up stage or add more week to exsiting SD      
                                        # print(sdForCleanUp)
                                        seir_modelProcessing(inputCSVPath,outputCSVPath,sirModelPath,beta,gamma,sigma)              # Run SEIR model 

                                        outputFile = Path(outputCSVPath)  
                                        if outputFile.is_file() == True:
                                            sdForCleanUp = cleanUpStage(sdForCleanUp)                                               # Clean up stage
                                            # print(sdForCleanUp)
                                            
                                            # Read output 
                                            node , riskLevel, stopFlag = readOutputCSVandFilter(outputCSVPath,sdForCleanUp,pigPop)  # Get dst SD, risk level and infected animal number from output file and filter with cleanup stage  
                                            sdRiskRecord = recordSDriskLevel(sdRiskRecord,riskLevel)
                                            # print(node)
                                    else:
                                        print('Cannot process this input file {}\nProgram will terminate'.format(inputCSVPath))
                                        break   
                                else:
                                    print('Not have potential starter nodes for week {}.'.format(weekNum))
                                    stopFlag = True                             
                                
                                loopEndNum = loopEndNum + 1

                            print('Finish!')
                            if len(sdRiskRecord) > 0 :
                                riskRecorded = {}                                                                                   # Convert list to dict and filter the duplicate one for recording 
                                riskRecorded = convertListToDict(sdRiskRecord)  
                                # print(riskRecorded) 
                                filterRisk(riskRecorded,riskLvlFolderPath,iniSD[0]) 

                        else:
                            print('No more initial node\nThe process is done')
                                
                            if len(sdRiskRecord) > 0 :
                                riskRecorded = {}                                                                                       # Convert list to dict and filter the duplicate one for recording 
                                riskRecorded = convertListToDict(sdRiskRecord) 
                                filterRisk(riskRecorded,riskLvlFolderPath,iniSD[0]) 
                    else:
                        print('No output for 2nd week from SIR model')
                        print('Finish!')
                        if len(sdRiskRecord) > 0 :
                            riskRecorded = {}                                                                                       # Convert list to dict and filter the duplicate one for recording 
                            riskRecorded = convertListToDict(sdRiskRecord)  
                            filterRisk(riskRecorded,riskLvlFolderPath,iniSD[0]) 
                            # print(riskRecorded)                                       
            else:
                print('\n\n{} not have potential starter node.\n\n'.format(iniSD[0]))
                # break 

            weekNum = 1

            # if riskRecorded is not None:
            #     filterRisk(riskRecorded,riskLvlFolderPath,iniSD[0])                                                             # Save risk file
            # else:
            #     print('There is no any infected animal.')

            print('Subdistrict: ' + str(iniSD[0] + ' done.\n\n\n'))  
                   
            # break
    else: # Special case for clean up process  (i.e. the infected SD cannot be use as a dst SD)  
        print('Special case')
        # Random seed number for simulating emovement
        import random
        seed = random.randrange(1000,9999)
        print('Random seed number: ' + str(seed))
        
        weekNum = 1  # Week number start with week 1        
        for iniSD in initSD: # Process through every initial SD
            print('Subdistrict: ' + str(iniSD[0] + ' process...'))

            sdRiskRecord = []
            sdForCleanUp = {}
            inputCSVPath = ''
            outputCSVPath = ''

            # Loop stoping flag
            stopFlag = False

            # >>>>>>>   FIRST WEEK   <<<<<<<<
            print('\n\nWeek number {} \n\n'.format(weekNum))

            # Simulate emove for : First Week
            simulateEmove_modelProcessing(sirModelPath,weekPath,realEmoveFile,initialSDFile,seed)
            
            curWeek  = sepEmoveToMatemove(weekPath + '/simMovement.csv', 1)
            curWeekList = convertDFtoList(curWeek)            

            curWeek.to_csv(weekPath + '/week_' + str(weekNum) + '.csv')
            

            emoveShorten = shortenEmoveTable(curWeekList,iniSD[0]) 
            # print(len(emoveShorten))
            if emoveShorten is not 0:                              
                inputCSVPath,outputCSVPath = firstWeekInputCSV(emoveShorten, pigPop, 
                                            iniSD[0], weekNum, weekPath, inputFolder, outPutFolder)
                
                if inputCSVPath != 0 and outputCSVPath != 0:       
                    sdForCleanUp = addTocleanUp(emoveShorten,sdForCleanUp,cleanUpPeriod)                                            # Add new dst SD to clean up stage or add more week to exsiting SD      
                    # print(sdForCleanUp)
                    seir_modelProcessing(inputCSVPath,outputCSVPath,sirModelPath,beta,gamma,sigma)                                  # Run SEIR model 
                    
                    from pathlib import Path
                    outputFile = Path(outputCSVPath)   

                    if outputFile.is_file() == True:                                                                                # Check whether output csv file is exist or not
                        # >>>>>>>  NEXT OTHER WEEK  <<<<<<<<
                        
                        # Read output 
                        node , riskLevel, stopFlag = readOutputCSVandFilter(outputCSVPath,sdForCleanUp,pigPop)                      # Get dst SD, risk level and infected animal number from output file and filter with cleanup stage
                        
                        sdRiskRecord = recordSDriskLevel(sdRiskRecord,riskLevel)                                                    # Record every output SD's risk level
                        # print(sdRiskRecord)

                        if stopFlag is False:                                                                                       # Stop flasg need to be false
                            loopEndNum = 0
                            loopEndMax = maxLoop                                                                                    # Maximum loop
                            while stopFlag is False and loopEndNum < loopEndMax:
                                
                                weekNum = weekNum + 1
                                print('\n\nWeek number {} \n\n'.format(weekNum))

                                initialSDforNweek = nextNweekInitialSD(node,workingDirectory)                                       # Create next initial node csv file for simulating emovement
                               
                                simulateEmove_modelProcessing(sirModelPath,weekPath,realEmoveFile,initialSDforNweek,seed)           # Simulate emove for : N Week
                                
                                curWeek  = sepEmoveToMatemove(weekPath + '/simMovement.csv', 1)
                                curWeekList = convertDFtoList(curWeek)
                                
                                curWeek.to_csv(weekPath + '/week_' + str(weekNum) + '.csv')
                                
                                # break
                                emoveShorten = shortenEmoveTable(curWeekList,node)
                                if emoveShorten is not 0:                              
                                    inputCSVPath,outputCSVPath = nWeekInputCSV(emoveShorten, pigPop, 
                                                                node,iniSD[0], weekNum, weekPath, inputFolder, outPutFolder)

                                    if len(loadCSV(inputCSVPath)) > 1:       
                                        sdForCleanUp = addTocleanUp(emoveShorten,sdForCleanUp,cleanUpPeriod)                        # Add new dst SD to clean up stage or add more week to exsiting SD      
                                        # print(sdForCleanUp)
                                        seir_modelProcessing(inputCSVPath,outputCSVPath,sirModelPath,beta,gamma,sigma)              # Run SEIR model 

                                        outputFile = Path(outputCSVPath)  
                                        if outputFile.is_file() == True:                                            
                                            # Read output 
                                            node , riskLevel, stopFlag = readOutputCSVandFilter(outputCSVPath,sdForCleanUp,pigPop)  # Get dst SD, risk level and infected animal number from output file and filter with cleanup stage  
                                            sdRiskRecord = recordSDriskLevel(sdRiskRecord,riskLevel)
                                            # print(node)
                                    else:
                                        print('Cannot process this input file {}\nProgram will terminate'.format(inputCSVPath))
                                        break   
                                else:
                                    print('Not have potential starter nodes for week {}.'.format(weekNum))
                                    stopFlag = True                             
                                
                                loopEndNum = loopEndNum + 1

                            print('Finish!')
                            if len(sdRiskRecord) > 0 :
                                riskRecorded = {}                                                                                   # Convert list to dict and filter the duplicate one for recording 
                                riskRecorded = convertListToDict(sdRiskRecord)  
                                filterRisk(riskRecorded,riskLvlFolderPath,iniSD[0])
                                # print(riskRecorded) 

                        else:
                            print('No more initial node\nThe process is done')
                            if len(sdRiskRecord) > 0 :
                                riskRecorded = {}                                                                                       # Convert list to dict and filter the duplicate one for recording 
                                riskRecorded = convertListToDict(sdRiskRecord) 
                                filterRisk(riskRecorded,riskLvlFolderPath,iniSD[0]) 
                    else:
                        print('No output for 2nd week from SIR model')
                        print('Finish!')
                        if len(sdRiskRecord) > 0 :
                            riskRecorded = {}                                                                                       # Convert list to dict and filter the duplicate one for recording 
                            riskRecorded = convertListToDict(sdRiskRecord) 
                            filterRisk(riskRecorded,riskLvlFolderPath,iniSD[0]) 
                            # print(riskRecorded) 
                                
            else:
                print('\n\n{} not have potential starter node.\n\n'.format(iniSD[0]))
                # break 

            weekNum = 1
            # filterRisk(riskRecorded,riskLvlFolderPath,iniSD[0])                                                                     # Save risk file
            print('Subdistrict: ' + str(iniSD[0] + ' done.\n\n\n'))            
            # break
               
    print('\n\n*************** The Risk Map creation is Done **************\n\n')

def main(workingDirectory = 'C:/', cleanUpPeriod=0 ,beta=10, gamma=0.5 ,sigma = 6.5, maxLoop = 40):
    print('Status of files and directories checking: '+ str(checkFileAndFolder(workingDirectory)))
    
    # File
    pigPopPath = workingDirectory +'/CATTLE_POP.csv'
    initialNodePath = workingDirectory + '/FMD_SubdistrictRisk_High.csv'
    emovementPath = workingDirectory + '/E_Movement_FMD_2017.csv'

    # Folder
    weekPath =  workingDirectory + '/Weeks'    
    inputFolder = workingDirectory + '/InputCSV'
    outPutFolder = workingDirectory +'/OutputCSV'
    riskLvlFolderPath = workingDirectory + '/RiskLevel'
    sirModelPath = workingDirectory +'/R_model'

    
    import csv       
    # Import every initial SD for first week
    initNodes = loadCSV(initialNodePath,1)

    # Import Animal population csv file
    pigReader = csv.reader(open(pigPopPath, 'r')) 
    tmpPop = []
    for row in pigReader:        
        tmp = []
        tmp.append(row[0])
        tmp.append(row[2])
        tmpPop.append(tmp)
    
    pigPop = dict(tmpPop)
    
    # Main process
    modelProcess(initNodes,pigPop,weekPath,emovementPath,initialNodePath,cleanUpPeriod,sirModelPath,beta,1/gamma,1/sigma,inputFolder,outPutFolder,workingDirectory,riskLvlFolderPath,maxLoop)
      
    #beg+++iKS03.02.2019 Update the results to the database
    import datetime
    import pyodbc
    from os import listdir

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

if __name__ == "__main__": 
    import argparse

    parser = argparse.ArgumentParser(description='Process the risk map with R model')
    parser.add_argument('workingDirect', metavar='Woring Directory',type=str,
                        help='working directory')
    parser.add_argument('cleanUpPeriod', metavar='Cleanup Stage',type=int, 
                        help='clean up period for cleaning the infected node in day format (Set 0 all the time)')
    parser.add_argument('beta', metavar='Beta',type=int, 
                        help='beta value (e.g. 10)')
    parser.add_argument('gamma', metavar='Gamma',type=float, 
                        help='gamma value (e.g. 0.5)')
    parser.add_argument('sigma', metavar='Sigma',type=float, 
                        help='sigma value (e.g. 6.5)')
                        
    args = parser.parse_args()    
    print(str(args.workingDirect) + '\n' + str(args.cleanUpPeriod) + '\n' + str(args.beta) + '\n' + str(args.gamma) + '\n' + str(args.sigma))
    main(workingDirectory=args.workingDirect, cleanUpPeriod=args.cleanUpPeriod, beta=args.beta, gamma=args.gamma,  sigma=args.sigma) 
    
    
    # python riskMapCreation.py D:/Works/RiskApp/Git_Source_Code 0 10 0.5
    
    # python riskMapCreation.py D:/RiskApp/SourceCode 14 10 0.5 6.5
    # python riskMapCreation.py D:/RiskApp/SourceCode 0 10 0.5
