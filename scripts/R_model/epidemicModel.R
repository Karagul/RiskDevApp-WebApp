args <- commandArgs(trailingOnly = T)

inputPath <- as.character(args[1])
outputPath <- as.character(args[2])
sirModelPath <- as.character(args[3])

#rootPath <- as.character(args[1])
#Directory----------------------------------------------------
#rootFolder <- normalizePath(rootPath, winslash = "/")
#setwd(file.path(rootFolder))

#codeFolder <- file.path(rootFolder, "SIR_code")
#outputFolder <- file.path(rootFolder, "Output")
#dataFolder <- file.path(rootFolder, "Data")

#dir.create(codeFolder, showWarnings=FALSE)
#dir.create(outputFolder, showWarnings=FALSE)
#dir.create(dataFolder, showWarnings=FALSE)

#Library------------------------------------------------------
if(!require(deSolve)){
  install.packages("deSolve")
  library(deSolve)
}
source(file.path(sirModelPath,"functionList.R"))

#Configuration-------------------------------------------------
Input <- read.csv(file = inputPath, header=FALSE, stringsAsFactors=FALSE)
caseList <- ListInput(Input)

#Parameters----------------------------------------------------
beta <- as.numeric(args[2])
gamma <- as.numeric(args[3])

parameters <- c(beta, gamma)
time <- seq(1,7,1) #days
#Execute--------------------------------------------------------
finalList <- NULL
for(i in 1:length(caseList)){
  populationSize <- caseList[[i]]$animalPopulation
  sourceSpec <- caseList[[i]]$sourceSpec
  destinationSpec <- caseList[[i]]$destinationSpec
  caseID <- caseList[[i]]$caseID
  addInfectedAnimal <- NULL
  addUninfectedAnimal <- NULL
  for(p in 1:length(sourceSpec)){
   sourceTotalAnimal <- sourceSpec[[p]]$sourceTotalAnimal
   sourceInfectAnimal <- sourceSpec[[p]]$sourceInfectAnimal
   sourceUnifectAnimal <- sourceTotalAnimal - sourceInfectAnimal
  
   addInfectedAnimal <- c(addInfectedAnimal, sourceInfectAnimal)
   addUninfectedAnimal <- c(addUninfectedAnimal, sourceUnifectAnimal)
   }
  Initial_S <- populationSize + sum(addUninfectedAnimal)
  Initial_I <- sum(addInfectedAnimal)
  Initial_R <- 0
  initial <- c(S=Initial_S, I=Initial_I, R=Initial_R)
  modelOutput <- as.data.frame(ode(initial, time, SIRModel, parameters))[,-1]
  roundModelOutput <-as.data.frame(matrix(round_preserve_sum(c(modelOutput$S, modelOutput$I, modelOutput$R), 0), ncol = 3))
  colnames(roundModelOutput) <- c("S", "I", "R")
  Day7_S <- roundModelOutput$S[7]
  Day7_I <- roundModelOutput$I[7]
  Day7_R <- roundModelOutput$R[7]
  totalInfect <- Day7_I
  totalNotInfect <- Day7_R + Day7_S
  listExport <- NULL
  for(d in 1:length(destinationSpec)){
    destinationID <- destinationSpec[[d]]$destinationID
    totalExport <- destinationSpec[[d]]$destinationAnimal
    exportInfect <- rhyper(1, totalInfect, totalNotInfect, totalExport)
    exportNotInfect <- totalExport - exportInfect
    totalInfect <- totalInfect - exportInfect
    totalNotInfect <- totalNotInfect - exportNotInfect
    totalPop <- totalInfect + totalNotInfect
    summaryExport <- c(destinationID, totalExport, exportInfect)
    listExport[[d]] <- summaryExport
    names(listExport) <- paste0("List", 1:d)
    }
  finalList[[i]] <- list(caseID=caseID, Export=listExport)
  names(finalList) <- paste0("Case", 1:i)
  
  for(i in 1:length(finalList)){
    if(finalList[[i]]$Export$List1[1] == 0){finalList[[i]] <- NULL}
  }
  
  totalCase<-length(finalList)
  Row1 <- c(totalCase, NA,NA)
  FINALDETAIL <- NULL
  allCaseDetail <- NULL
  for(i in 1:length(finalList)){
    caseSpec <- finalList[[i]]
    caseID <- caseSpec$caseID
    exportNumber <- length(caseSpec$Export)
    caseDetail <- c(caseID, exportNumber, NA)
    allDestination <- NULL
    for(e in 1:length(caseSpec$Export)){
      exportSpec <- caseSpec$Export[[e]]
      destinationID <- exportSpec[1]
      destinationAnimal <- exportSpec[2]
      destinationInfectAnimal <- exportSpec[3]
      destinationDetail <- c(destinationID,destinationAnimal, destinationInfectAnimal)
      allDestination <- as.matrix(rbind(allDestination,destinationDetail))
      unname(allDestination)
    }
    allCaseDetail <- rbind(caseDetail, allDestination)
    FINALDETAIL<- rbind(FINALDETAIL,allCaseDetail)
  }
  output <- unname(rbind(Row1, FINALDETAIL))
  output[is.na(output)] <- ""
  }
output

write.table(output, file = outputPath, col.names = F, row.names = F, sep = ",")
