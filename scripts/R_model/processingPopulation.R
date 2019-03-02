args <- commandArgs(trailingOnly = T)

rootPath <- as.character(args[1]) #working directory
csvFile <- as.character(args[2]) #csv file name (ex. DataMart_AnimalPopulation.csv)

#Directory-------------------------------------------------------------------------------
# rootFolder <- normalizePath(rootPath, winslash = "/")
# setwd(file.path(rootFolder))
# csvDir <- paste(rootPath, csvFile, sep = "/")

#Importing data--------------------------------------------------------------------------
inputData <- read.csv(csvFile)

#Selecting data---------------------------------------------------------------------------
diseaseList <- c("FMD" ,"HPAI" , "nipah") #Configure here
FMDCode <- c('10202','10207')
HPAICode <- c('30101','30103','30108','30110','30301','30801')
nipahCode <- c("20101")
codeList <- list(FMDCode, HPAICode, nipahCode) #make sure it is in the right order
inputData$SourceYear <- inputData$SourceYear - 543
Column <- c("SubDistrictCode","ANIMAL_CODE", "AnimalTotal", "SourceYear")

for(i in 1:length(diseaseList)){
  disease <- diseaseList[i]
  animalCode <- codeList[[i]]
  animalFlag <- which(inputData$ANIMAL_CODE %in% animalCode)
  subsetData <- inputData[animalFlag, Column]
  yearList <- sort(unique(subsetData$SourceYear))
  for(j in 1:length(yearList)){
    yearID <- yearList[j]
    yearFlag <- which(subsetData$SourceYear == yearID)
    Data <- subsetData[yearFlag, -which(colnames(subsetData) == "SourceYear")]
    outputName <- paste("Population",disease, yearID, sep = "_")
    write.table(Data, paste0(paste(rootPath, outputName, sep = "/"), ".csv"), sep = ",", row.names = F)
  }
}
