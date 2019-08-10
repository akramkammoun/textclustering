<?php
function creationImmContext($corpusPurifieFichier,$motsCorpusAlloueFichier,$fichierImm,$fichierImmContext)
{
    
    $motsCorpusAlloueTab = file($motsCorpusAlloueFichier,FILE_IGNORE_NEW_LINES);
    $nbreMotsCorpus = count($motsCorpusAlloueTab);
    //creation l'entete de matrice et creation une chaine de caractere qui contient $nbreMotsCorpus 0(zero)
    //$matDocTermeLigne est sous la forme : 0\t0\t0\t...0\n => taille : $nbreMotsCorpus*2
    //RQ: \t est un caractere

    //variable intermidiaire
    $matDocTermeLigne = "";//ligne de la matrice doc-terme.
    //

    $motsCorpusAlloueLigne = "";//pour ecrire l'entete
    for($i=0;$i<$nbreMotsCorpus;$i++)
    	for($j=0;$j<$nbreMotsCorpus;$j++)
		$matrice[$i][$j]=0;

    if(!file_exists($corpusPurifieFichier))
        die("Erreur dans la fonction creationImmContext, la source de probleme
            est matriel. Veuillez recommencer.");
    
    $corpusPurifieFlux=fopen($corpusPurifieFichier,"r");
    //while(!feof($corpusPurifieFlux))
    //while ($compPhraseCorpusPurifie < $nbrePhrasesCorpusPurifie)//nbrePhrase lignes
    while(!feof($corpusPurifieFlux))
    {
        //supprimer \n de la phrase purifie
        $phraseCorpusPurifie = rtrim(fgets($corpusPurifieFlux),"\n");

        if(!feof($corpusPurifieFlux))//on elemine la dernier phrase qui est tjrs vide
        {
            //faire une copie de $matDocTermeLigne pour modifier $matDocTermeLigneTest.
            $matDocTermeLigneTest = $matDocTermeLigne;

            $phraseCorpusPurifieTab = explode("\t", $phraseCorpusPurifie);

            for($i=0;$i<sizeof($phraseCorpusPurifieTab)-1;$i++)
            {
                //retourne l'indice de motCorpus dans $motsCorpusAlloueTab
                $indiceMotCorpusligne = array_search($phraseCorpusPurifieTab[$i], $motsCorpusAlloueTab);
				$indiceMotCorpuscolone = array_search($phraseCorpusPurifieTab[$i+1], $motsCorpusAlloueTab);

                //calculer l'indice dans la chaine $matDocTermeLigneTest
                $matrice[$indiceMotCorpusligne][$indiceMotCorpuscolone]++;

                //maj de la chaine $matDocTermeLigneTest


            }
            //ecrire $matDocTermeLigneTest modifi�

        }
    }
    //fermer les connections

    if(!file_exists($fichierImm))
        die("Erreur dans la fonction creationImmContext, la source de probleme
             est matriel. Veuillez recommencer.");

    $fimm = fopen($fichierImm, "r");
    $cont=fgets($fimm);
    $nbmot=0;
    while(!feof($fimm))
    {
            $cont=rtrim(fgets($fimm));
            if(!feof($fimm))
            {
                $imm[$nbmot]=explode("\t",$cont);
                $nbmot++;
            }
    }
    //immdroit
    $ch="";
    for($i=0;$i<$nbreMotsCorpus;$i++)
    {
    $som[$i]=0;
    for($j=0;$j<$nbreMotsCorpus;$j++)
            {
                    $som[$i]=$som[$i]+$matrice[$i][$j];
                    $imm_contextdroit[$i][$j]=0;
                    //$imm_contextgauche[$i][$j]=0;
            }
    }
   for($i=0;$i<$nbreMotsCorpus;$i++)
   {
        for($j=0;$j<$nbreMotsCorpus;$j++)
        if($matrice[$i][$j]>0)
                for($k=0;$k<$nbreMotsCorpus;$k++)
                {
                        $imm_contextdroit[$i][$k]=$imm_contextdroit[$i][$k]+(($imm[$k][$j]*$matrice[$i][$j])/$som[$i]);
                        //$imm_contextgauche[$i][$k]=$imm_contextgauche[$i][$k]+(($imm[$k][$j]*$matrice[$j][$i])/$somg[$i]);
                }
        }

        for($i=0;$i<$nbreMotsCorpus;$i++)
                for($j=0;$j<$nbreMotsCorpus;$j++)
                        $matricegauche[$i][$j]=$matrice[$j][$i];
        for($i=0;$i<$nbreMotsCorpus;$i++)
        {
        $som[$i]=0;
        for($j=0;$j<$nbreMotsCorpus;$j++)
                {
                        $som[$i]=$som[$i]+$matricegauche[$i][$j];
                        $imm_contextgauche[$i][$j]=0;
                        //$imm_contextgauche[$i][$j]=0;
                }
        }
   for($i=0;$i<$nbreMotsCorpus;$i++)
   {
        for($j=0;$j<$nbreMotsCorpus;$j++)
        if($matricegauche[$i][$j]>0)
                for($k=0;$k<$nbreMotsCorpus;$k++)
                {
                        $imm_contextgauche[$i][$k]=$imm_contextgauche[$i][$k]+(($imm[$k][$j]*$matricegauche[$i][$j])/$som[$i]);
                        //$imm_contextgauche[$i][$k]=$imm_contextgauche[$i][$k]+(($imm[$k][$j]*$matrice[$j][$i])/$somg[$i]);
                }
        }




    //$fcorpus = fopen("immcontextdroit1.txt", "w");
    //fwrite($fcorpus,$ch);
    fusiontableau($imm,$imm_contextdroit,$imm_contextgauche,$tab);
    $ch="";
    for($i=0;$i<$nbreMotsCorpus-1;$i++)
    $ch.=trim( $motsCorpusAlloueTab[$i])."\t".trim( $motsCorpusAlloueTab[$i])."\t".trim( $motsCorpusAlloueTab[$i])."\t";
    $ch.=trim( $motsCorpusAlloueTab[$i])."\t".trim( $motsCorpusAlloueTab[$i])."\t".trim( $motsCorpusAlloueTab[$i])."\n";
    for($i=0;$i<$nbreMotsCorpus-1;$i++)
    {
            $ch.=implode("\t",$tab[$i]);
            $ch.="\n";
    }
    $ch.=implode("\t",$tab[$i]);
            $ch.="\n";
    $fcorpus = fopen($fichierImmContext, "w");
    fwrite($fcorpus,$ch);
    
}
function fusiontableau($imm,$imm_contextdroit,$imm_contextgauche,&$tab)
{
	for($i=0;$i<sizeof($imm_contextdroit);$i++)
	{
		for($j=0;$j<sizeof($imm[$i])*3;$j=$j+3)
		{
			$tab[$i][$j]=trim($imm[$i][$j/3]);
			$tab[$i][$j+1]=$imm_contextdroit[$i][$j/3];
			$tab[$i][$j+2]=$imm_contextgauche[$i][$j/3];
		}	
	}
}
//creation_mat_doc_terme("corpus.txt","mot.txt","imm.txt","immmodif.txt")
?>
