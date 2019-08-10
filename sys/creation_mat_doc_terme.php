<?php

function creationMatDocTerme($corpusPurifieFichier,$motsCorpusAlloueFichier,$matDocTermeFichier)
{
    
    //lire les fichiers
    //FILE_IGNORE_NEW_LINES : pour supprimer \n apres chaque ligne.
    $motsCorpusAlloueTab = file($motsCorpusAlloueFichier,FILE_IGNORE_NEW_LINES);
    $nbreMotsCorpus = count($motsCorpusAlloueTab);
    //creation l'entete de matrice et creation une chaine de caractere qui contient $nbreMotsCorpus 0(zero)
    //$matDocTermeLigne est sous la forme : 0\t0\t0\t...0\n => taille : $nbreMotsCorpus*2
    //RQ: \t est un caractere

    //variable intermidiaire
    $matDocTermeLigne = "";//ligne de la matrice doc-terme.
    //

    $motsCorpusAlloueLigne = "";//pour ecrire l'entete
    for($i=0;$i<$nbreMotsCorpus-1;$i++)
    {
        $matDocTermeLigne .= "0\t";
        $motsCorpusAlloueLigne .= $motsCorpusAlloueTab[$i]."\t";
    }
    $matDocTermeLigne .= "0\n";
    $motsCorpusAlloueLigne .= $motsCorpusAlloueTab[$nbreMotsCorpus-1]."\n";

    if(!file_exists($corpusPurifieFichier))
        die("Erreur dans la fonction creationMatDocTerme, la source de probleme
            est matriel. Veuillez recommencer.");

    //ouvrir une connexion $corpusPurifieFlux
    $corpusPurifieFlux = fopen($corpusPurifieFichier,"r");
    //ouvrir une connexion $matDocTermeFlux
    $matDocTermeFlux = fopen($matDocTermeFichier,"w");

    //ecrire l'entete dans le fichier $matDocTermeFichier
    fwrite($matDocTermeFlux,$motsCorpusAlloueLigne);

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

            foreach ($phraseCorpusPurifieTab as $motphraseCorpusPurifie)
            {
                //retourne l'indice de motCorpus dans $motsCorpusAlloueTab
                $indiceMotCorpus = array_search($motphraseCorpusPurifie, $motsCorpusAlloueTab);

                //calculer l'indice dans la chaine $matDocTermeLigneTest
                $indiceMotCorpusLigneMatDocTermeTest = $indiceMotCorpus*2;

                //maj de la chaine $matDocTermeLigneTest
                $matDocTermeLigneTest[$indiceMotCorpusLigneMatDocTermeTest] = "1";

            }
            //ecrire $matDocTermeLigneTest modifié
            fwrite($matDocTermeFlux,$matDocTermeLigneTest);
        }
    }
    //fermer les connections
    fclose($matDocTermeFlux);
    fclose($corpusPurifieFlux);
}
?>
