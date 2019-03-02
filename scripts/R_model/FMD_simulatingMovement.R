args <- commandArgs(trailingOnly = T)

outputPath <- as.character(args[1]) # output directory
csvFile <- as.character(args[2]) #"EmoveData_nipah_2017.csv" 
csvInitialSD <-  as.character(args[3]) #"InitialSD.csv"
seed <- as.numeric(args[4]) #11111

#---------------------------------------------------------------------------------------
if(!require(truncnorm)){
  install.packages("truncnorm")
  library(truncnorm)
}

#Directory-------------------------------------------------------------------------------
# rootFolder <- normalizePath(rootPath, winslash = "/")
# setwd(file.path(rootFolder))
# csvDir <- paste(rootPath, csvFile, sep = "/")

#Importing Data-------------------------------------------------------------------------
initialNodeData <- read.csv(csvInitialSD)
initialNode <- initialNodeData$SD_CODE
inputData <- read.csv(csvFile)

inputData$EmoveDate <- as.Date(inputData$EmoveDate)
inputData$WeekID <- strftime(inputData$EmoveDate, format = "%V")

flag <- c(which(inputData$ANI_AMT == 0) ,which(inputData$Source_SubDistrictCode == inputData$Destination_SubDistrictCode))
column <- c("Source_SubDistrictCode"     , "Destination_SubDistrictCode" ,"ANI_AMT", "WeekID")
cleanedData <- inputData[-flag, column]
cleanedData$frequency <- rep(1, nrow(cleanedData))
#Subseting Data >>> only initial nodes-------------------------------------------------
set.seed(seed)
flagInitial<-which(cleanedData$Source_SubDistrictCode %in% initialNode)
subsetData <- cleanedData[flagInitial,]

sourceCheck <- initialNode %in% subsetData$Source_SubDistrictCode
if(all(!sourceCheck)) { stop("No animal movement generated")} else {
  if(!all(sourceCheck)) {initialNode <- initialNode[-which(sourceCheck == F)]}
  
  simData <- NULL
  for(i in 1:length(initialNode)){
    sourceNode <- initialNode[i]
    sourceFlag <- which(subsetData$Source_SubDistrictCode == sourceNode)
    sourceMove <- subsetData[sourceFlag,]
    weeklyExport <- aggregate(frequency ~ WeekID, sourceMove, sum)
    alpha <- mean(weeklyExport$frequency)
    timesMovement <- rpois(1,alpha)
    destinationList <- aggregate(frequency ~ Destination_SubDistrictCode, sourceMove, sum)
    if(timesMovement == 0){timesMovement <- 1}
    destinationList$prob <- destinationList$frequency/sum(destinationList$frequency)
    destination <- sample(as.character(destinationList$Destination_SubDistrictCode), size =timesMovement, prob =  destinationList$prob, replace = T)
    simMovementAll <- NULL
    for(j in 1:timesMovement){
      destinationID <- destination[j]
      destinationFlag <- which(sourceMove$Destination_SubDistrictCode == destinationID)
      animalAmountList <- sourceMove[destinationFlag,]$ANI_AMT
      if(length(animalAmountList) <= 1){movementAmount <- animalAmountList} else {
      movementAmount <- round(rtruncnorm(n = 1,a = 1, mean = mean(animalAmountList), sd = sd(animalAmountList)))}
      simMovement <- as.data.frame(cbind(Source_SubDistrictCode = sourceNode, Destination_SubDistrictCode = destinationID, ANI_AMT = movementAmount))
      simMovementAll <- rbind(simMovementAll, simMovement)
    }
    simMovementAll
    simData <- rbind(simData, simMovementAll)
  }
  
  simData$ANI_AMT <- as.numeric(simData$ANI_AMT)
  simData <- aggregate(ANI_AMT ~ Source_SubDistrictCode + Destination_SubDistrictCode, simData, sum)
  
  
  #Exporting outputs-----------------------------------------------------------------------------------------------------------------------------------
  
 write.table(simData, paste(outputPath, "simMovement.csv", sep = "/"), sep = ",",row.names = F)
  
  
}





