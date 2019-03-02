import subprocess

def main(directory, modelPath='R_model', fileName='DataMart_Emove.csv', slaughterinclude=True):
    print('Running R model => START')
    slauter = ''
    if slaughterinclude: slauter='with'
    else: slauter = 'without'

    try:
        subprocess.call ("/usr/local/bin/Rscript --vanilla {0}/processingEmove.R {1} {2} {3}".format(modelPath,directory,fileName,slauter), shell=True)
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
    parser.add_argument('datamartEmoveFile', metavar='DataMart_Emove file',type=str, 
                        help='Original DataMart_Emove csv file')
    parser.add_argument('slaughter', metavar='with or without slaughter',type=bool, 
                        help='Include slaughter or not (True or False)')

    args = parser.parse_args()
    print(str(args.workingDirect) + '\n' + str(args.rModelFolfer) + '\n' + str(args.datamartEmoveFile) + '\n' + str(args.slaughter))
    main(directory=args.workingDirect ,modelPath=args.rModelFolfer, fileName=args.datamartEmoveFile, slaughterinclude=args.slaughter) 

    # main('D:/Works/RiskApp/Git_Source_Code', 'D:/Works/RiskApp/Git_Source_Code/R_model', 'D:/Works/RiskApp/DataMart_Emove.csv', True) 
    
    # python processEmove.py D:/Works/RiskApp/Git_Source_Code D:/Works/RiskApp/Git_Source_Code/R_model D:/Works/RiskApp/DataMart_Emove.csv True
    
    # python processEmove.py D:/RiskApp/SourceCode D:/RiskApp/SourceCode/R_model D:/RiskApp/SourceCode/DataMart_Emove.csv True