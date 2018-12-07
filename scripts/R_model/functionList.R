#SIR_Model----------------------------------------------------------------
SIRModel <- function(time, state, parameter){
  S <- state[1]
  I <- state[2]
  R <- state[3]
  with(
    as.list(parameter),
    {
      dS = -beta * S * I
      dI = beta * S * I - gamma * I
      dR = gamma * I
      return(list(c(dS, dI, dR)))
    }
  )
}

#Round_preserve_sum-------------------------------------------------------
round_preserve_sum <- function(x, digits = 0) {
  up <- 10 ^ digits
  x <- x * up
  y <- floor(x)
  indices <- tail(order(x-y), round(sum(x)) - sum(y))
  y[indices] <- y[indices] + 1
  y / up
}

#Case_list------------------------------------------------------------------
ListInput <- function(Input){
  caseNumber <- as.numeric(as.character(Input[1,1]))
  caseRow <- NULL
  for(i in 2:nrow(Input)){
    if(!is.na(Input[i,4])){caseRow <- c(caseRow,i)}
  }
  caseList <- list()
  for(i in 1:caseNumber){
    caseRowID <- caseRow[i]
    caseID <- as.character(Input[caseRowID,1])
    sourceNumber <- Input[caseRowID,2] 
    destinationNumber <- Input[caseRowID,3] 
    animalPopulation <- Input[caseRowID,4]
    sourceSpec <- list()
    for(m in 1:sourceNumber){
      sourceRow <- Input[caseRowID+m,]
      sourceID <- as.character(sourceRow[1,1])
      sourceTotalAnimal <- as.numeric(sourceRow[2])
      sourceInfectAnimal <- as.numeric(sourceRow[3])
      sourceSpec[[m]] <- list(sourceID = sourceID, sourceTotalAnimal =sourceTotalAnimal , sourceInfectAnimal=sourceInfectAnimal)
    }
    destinationSpec <- list()
    for(d in 1:destinationNumber){
      if(destinationNumber > 0){ destinationRow <- Input[caseRowID+sourceNumber+d,]} else {destinationRow <- NA}
      destinationID <- as.character(destinationRow[1,1])
      destinationAnimal <- as.numeric(destinationRow[2])
      if(destinationNumber > 0){destinationSpec[[d]] <- list(destinationID = destinationID, destinationAnimal = destinationAnimal)} else {destinationSpec[[1]] <- list(destinationID = 0, destinationAnimal = 0)}
    }
    sourceName <- paste0("Source", 1:sourceNumber)
    names(sourceSpec) <- sourceName
    if(destinationNumber >0) {destinationName <- paste0("destination", 1:destinationNumber)} else {destinationName <- paste0("destination", 1)}
    names(destinationSpec) <- destinationName
    caseList[[i]] <- list(caseID = caseID, sourceSpec = sourceSpec, animalPopulation=animalPopulation, destinationSpec = destinationSpec)
  }
  caseName <- paste0("Case", 1:caseNumber)
  names(caseList) <- caseName
  return(caseList)
}