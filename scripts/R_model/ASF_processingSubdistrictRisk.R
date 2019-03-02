args <- commandArgs(trailingOnly = T)

outPutDirectory <- as.character(args[1]) # output directory
csvFile <- as.character(args[2]) # csv file name (ex. Subdistrict_risk.csv)

#Directory-------------------------------------------------------------------------------
# rootFolder <- normalizePath(rootPath, winslash = "/")
# setwd(file.path(rootFolder))
# csvDir <- paste(rootPath, csvFile, sep = "/")

inputData <- read.csv(csvFile)
#----------------------------------------------------------------------------------------
Column <- c("SD_CODE", "RiskLevel")
riskData <- inputData[, Column]

riskList <- as.character(unique(riskData$RiskLevel))
for(i in 1:length(riskList)){
  riskID <- riskList[i]
  riskFlag <- which(riskData$RiskLevel == riskID)
  subsetData <- riskData[riskFlag,]
  
  outputName <- paste("ASF_SubdistrictRisk", riskID, sep = "_")
  write.table(subsetData, paste0(paste(outPutDirectory, outputName, sep = "/"), ".csv"), sep = ",", row.names = F)
}

