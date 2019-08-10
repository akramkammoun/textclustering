<?php
//require ("includes/efficacite_concepts.php");
//require ("includes/extraire_concepts.php");



function classificationIMM($sepOS,$nbreMotsCorpusAlloue,$nbreConceptsExpert,$expertConceptFichier,$conceptsRResultatDir,$conceptsResultatDir,
        $conceptsEfficacitesDir,$typeMethodeTabNbreMotMin,$typeMethodeTabMarge,$methodeTabName,$methodeTabNbreTest,
        $typeMethodeTabName,$typeMethodeTabNbreMotMax)
{
    $lengthMethodeTab = count($methodeTabName);#tjrs fixe
    $lengthTypeMethodeTab = count($typeMethodeTabName);#tjrs fixe

    for($methodeNum = 0;$methodeNum < $lengthMethodeTab;$methodeNum++)
    {
        $methode = $methodeTabName[$methodeNum];
        $nbreTest = $methodeTabNbreTest[$methodeNum];

        $nomFichierEfficacite = "{$methode}_efficacite.txt";
        $chemainEfficacite = $conceptsResultatDir.$sepOS.$methode.$sepOS.$nomFichierEfficacite;

        $entete = $methode;
        //si $nbreTest=1 donc il y'aura qu'une seul eff qu'on appel eff_moy
        if($nbreTest != 1)
        {
            for($i=1;$i<=$nbreTest;$i++)
            {
                $entete .= "\teff_".$i;
            }
        }
        //construction dir mehode
        $dirMethode = $conceptsResultatDir.$sepOS.$methode;
        @mkdir($dirMethode,0777,TRUE);

        $entete .= "\teff_moy\n";
        $fichierEff = fopen($chemainEfficacite,"w");
        fwrite($fichierEff,$entete);
        fclose($fichierEff);


        //echo $methode."<br>";

        for($typeMethodeNum = 0;$typeMethodeNum < $lengthTypeMethodeTab;$typeMethodeNum++)
        {
            $typeMethode = $typeMethodeTabName[$typeMethodeNum];
            $nbreMotsMax = $typeMethodeTabNbreMotMax[$typeMethodeNum];
            $nbreMotMin = $typeMethodeTabNbreMotMin[$typeMethodeNum];

            //echo "___".$typeMethode."<br>";

            while($nbreMotsMax >= $nbreMotMin)
            {
                $dirClasses = $conceptsResultatDir.$sepOS.$methode.$sepOS."classes".
                        $sepOS.$typeMethode.$sepOS.$nbreMotsMax."_mots".$sepOS;
                //creation $dirClasses
                @mkdir ($dirClasses,0777,TRUE) ;

                $dirClasses_r = $conceptsRResultatDir.$sepOS.$methode.$sepOS."classes_r".$sepOS.
                                    $typeMethode.$sepOS.$nbreMotsMax."_mots".$sepOS;

                //creation $typeMethode
                $fichierEff = fopen($chemainEfficacite,"a");
                fwrite($fichierEff,$typeMethode."_".$nbreMotsMax);
                fclose($fichierEff);

                $effGlobale = 0;
                for($i=1;$i<=$nbreTest;$i++)
                {

                    $fichierClasses_r = $dirClasses_r."{$methode}_{$typeMethode}_{$nbreMotsMax}_{$i}_classe_r.txt";
                    $fichierClasses = $dirClasses."{$methode}_{$typeMethode}_{$nbreMotsMax}_{$i}_classe.txt";

                    extraireConcepts($fichierClasses_r,$nbreMotsCorpusAlloue,$nbreConceptsExpert,$fichierClasses);

                    $eff = efficacite($fichierClasses,$expertConceptFichier,$nbreMotsCorpusAlloue);

                    $effGlobale += $eff;

                    //creation  de tout les eff de nbreTest
                    $fichierEff = fopen($chemainEfficacite,"a");
                    fwrite($fichierEff,"\t".$eff);
                    fclose($fichierEff);
                }
                //si $nbreTest=1 donc il y'aura qu'une seul eff qu'on appel eff_moy
                if($nbreTest != 1)
                {
                    $effGlobale = $effGlobale/$nbreTest;
                    //creation de effGlobale
                    $fichierEff = fopen($chemainEfficacite,"a");
                    fwrite($fichierEff,"\t".$effGlobale."\n");
                    fclose($fichierEff);
                }
                else
                {
                    //creation de effGlobale
                    $fichierEff = fopen($chemainEfficacite,"a");
                    fwrite($fichierEff,"\n");
                    fclose($fichierEff);
                }
                $marge = $typeMethodeTabMarge[$typeMethodeNum];
                $nbreMotsMax -= $marge;
            }
        }
        //copy tout les efficacites de tout les methode dans un seul dossier
        $chemainEfficaciteProjet = $conceptsEfficacitesDir.$sepOS.$nomFichierEfficacite;
        copy($chemainEfficacite,$chemainEfficaciteProjet);

    }
    //echo "=>extraction accomplie";
}

?>
