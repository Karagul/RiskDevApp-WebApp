args <- commandArgs(trailingOnly = T)

rootPath <- as.character(args[1]) # output directory
csvFile <-  as.character(args[2]) # csv file name (ex. EmoveData.csv)
slaughter <- as.character(args[3]) # "with" or "without" slaughter 

#Directory-------------------------------------------------------------------------------
# rootFolder <- normalizePath(rootPath, winslash = "/")
# setwd(file.path(rootFolder))
# csvDir <- paste(rootPath, csvFile, sep = "/")

#Importing data--------------------------------------------------------------------------
inputData <- read.csv(csvFile)

#Selecting species----------------------------------------------------------------------
diseaseList <- c("nipah", "FMD", "HPAI")

nipahCode <- as.character(20102:20110)
FMDCode <- as.character(c(10201,	10202,	10204,	10205,	10207,	10208,	10209,	10210,	10212,10213	,10214,	10215,	10216))
HPAICode <- as.character(c(30101,	30102	,30103,	30104	,30105	,30108,	30110,	30112,	30113,	30202,
                           30203,	30204,	30206,	30207,	30208,	30302,	30304,	30305,	30306,	30309,
                           30401,	30402,	30501,	30502,	30601,	30701,	30801,	30901,	31001,	31002,
                           31003,	31004,	31007,	31009,	31199,	31299))

codeList <- list(nipahCode, FMDCode, HPAICode)
inputData$SourceYear <- inputData$SourceYear - 543

inputData$EmoveDate <- as.Date(inputData$EmoveDate)
inputData$Year <- format(inputData$EmoveDate, '%Y')
inputData$Month <- format(inputData$EmoveDate, '%m')
inputData$Day <- format(inputData$EmoveDate, '%d')

slaughterFlag <- which(inputData$ANI_OBJE_NAME == '����ç���')
if(slaughter == "without"){inputData <- inputData[-slaughterFlag,]}

Column <- c("Source_SubDistrictCode", "Destination_SubDistrictCode", "ANI_AMT", "EmoveDate", "Year", "Month", "Day", "ANIMAL_CODE")

for(i in 1:length(diseaseList)){
  disease <- diseaseList[i]
  animalCode <- codeList[[i]]
  animalFlag <- which(inputData$ANIMAL_CODE %in% animalCode)
  subsetData <- inputData[animalFlag, Column]
      yearList <- sort(unique(subsetData$Year))
      for(j in 1:length(yearList)){
        yearID <- yearList[j]
        yearFlag <- which(subsetData$Year == yearID)
        Data <- subsetData[yearFlag, -which(colnames(subsetData)=="Year")]
        if(slaughter == "without"){outputName <- paste("E_Movement", disease, "NoSL", yearID, sep = "_")} else{outputName <- paste("E_Movement", disease, yearID, sep = "_")}
        write.table(Data, paste0(paste(rootPath, outputName, sep = "/"), ".csv"), sep = ",", row.names = F)
  }
}


