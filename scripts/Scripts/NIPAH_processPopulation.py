import subprocess

def main(modelPath , directory , fileName):
    print('Running R model => START')
    try:
        #beg+++eKS26.02.2019 Change R model path
        #subprocess.call ("Rscript --vanilla {0}/processingPopulation.R {1} {2}".format(modelPath,directory,fileName), shell=True)
        subprocess.call ("/usr/local/bin/Rscript --vanilla {0}processingPopulation.R {1} {2}".format(modelPath,directory,fileName), shell=True)
        #end+++eKS26.02.2019 Change R model path
        print('Running R model => DONE')
    except:
        print('Running R model => FAILED')    


if __name__ == "__main__": 
    import argparse

    parser = argparse.ArgumentParser(description='Process the cutting animal population file for each disease')
    parser.add_argument('workingDirect', metavar='Woring Directory',type=str,
                        help='working directory')
    parser.add_argument('rModelFolfer', metavar='R model folder',type=str, 
                        help='R models folder')
    parser.add_argument('datamartAniPopFile', metavar='DataMart animal population file',type=str, 
                        help='Original DataMart_AnimalPopulation csv file')
    

    args = parser.parse_args()
    print(str(args.workingDirect) + '\n' + str(args.rModelFolfer) + '\n' + str(args.datamartAniPopFile) )
    main(directory=args.workingDirect ,modelPath=args.rModelFolfer, fileName=args.datamartAniPopFile) 
    
    # main('D:/Works/RiskApp/Git_Source_Code/R_model',  'D:/Works/RiskApp/Git_Source_Code', 'D:/Works/RiskApp/DataMart_AnimalPopulation.csv')   
    
    # python processPopulation.py D:/Works/RiskApp/Git_Source_Code D:/Works/RiskApp/Git_Source_Code/R_model D:/Works/RiskApp/DataMart_AnimalPopulation_2017.csv
    
    # python processPopulation.py D:/RiskApp/SourceCode D:/RiskApp/SourceCode/R_model D:/RiskApp/SourceCode/DataMart_AnimalPopulation_2017.csv