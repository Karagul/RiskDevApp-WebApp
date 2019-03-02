import subprocess

def main(directory, modelPath , fileName):
    print('Running R model => START')
    try:
        #beg+++eKS22.02.2019 Changing R script location
        #subprocess.call ("Rscript --vanilla {0}/processingSubdistrictRisk.R {1} {2}".format(modelPath,directory,fileName), shell=True)
        subprocess.call("/usr/local/bin/Rscript --vanilla {0}/ASF_processingSubdistrictRisk.R {1} {2}".format(modelPath, directory, fileName), shell=True)
        #end+++eKS22.02.2019 Changing R script location
        print('Running R model => DONE')
    except:
        print('Running R model => FAILED')    


if __name__ == "__main__": 
    import argparse

    parser = argparse.ArgumentParser(description='Process the cutting emovement file for each disease')
    parser.add_argument('workingDirect', metavar='Woring Directory',type=str,
                        help='working directory')
    parser.add_argument('rModelFolfer', metavar='R model folder',type=str, 
                        help='R models folder')
    parser.add_argument('subdistrictRiskFile', metavar='Subdistrict Risk File', 
                        help='Original sub-district risk csv file')
    
    args = parser.parse_args()    
    print(str(args.workingDirect) + '\n' + str(args.rModelFolfer) + '\n' + str(args.subdistrictRiskFile))
    main(directory=args.workingDirect ,modelPath=args.rModelFolfer, fileName=args.subdistrictRiskFile)     
    # main('D:/Works/RiskApp/Git_Source_Code', 'D:/Works/RiskApp/Git_Source_Code/R_model', 'D:/Works/RiskApp/Subdistrict_risk.csv') 

    # python processSubdistrictRisk.py D:/Works/RiskApp/Git_Source_Code D:/Works/RiskApp/Git_Source_Code/R_model D:/Works/RiskApp/Subdistrict_risk.csv

    # python processSubdistrictRisk.py D:/RiskApp/SourceCode D:/RiskApp/SourceCode/R_model D:/RiskApp/SourceCode/Subdistrict_risk.csv