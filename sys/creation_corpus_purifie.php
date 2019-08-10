<?php
/*BUT:
 *
 * creation fichiers donnee: corpus_purifie, mots_corpus_purifie
 * creation fichier configuration qui contient: nbrePhraseCorpusPurifie, nbreMotsCorpusPurifie
 * creation fichier statistique qui contient: nbreMotsCorpus \ {stopListe}
 *
 *
 *
 */
function creationCorpusPurifie($corpusPurifieStopListFichier,$motsCorpusNonAlloueFichier,
        $motsCorpusAlloueFichier,$constantesConfigurationFichier,$corpusPurifieFichier)
{

    //variables intermidiaire
        //$motsCorpusAlloueOuNonAlloueFichier;
        //$utiliseMotCorpusAlloue;
    //variable de retour:
    $nbrePhraseCorpusPurifie = 0;

    //creation corpus purifie

    if(!file_exists($corpusPurifieStopListFichier) || !file_exists($motsCorpusAlloueFichier)
            || !file_exists($motsCorpusNonAlloueFichier))
        die("Erreur dans la fonction creationCorpusPurifie, la source de probleme
            est matriel. Veuillez recommencer.");
    //
    //comparer $motsCorpusNonAlloueFichier et $motsCorpusAlloueFichier
    //$motsCorpusNonAlloueFichier est plus petit on a va travailler avec pour la purification
    //si non on va travailler avec $motsCorpusAlloueFichier. Et ca c'est pour gagner du temps.

    if(filesize($motsCorpusNonAlloueFichier) < filesize($motsCorpusAlloueFichier))
    {
        $motsCorpusAlloueOuNonAlloueTab = file($motsCorpusNonAlloueFichier,
             FILE_IGNORE_NEW_LINES);
        $utiliseMotCorpusAlloue = false;
    }
    else
    {
        $motsCorpusAlloueOuNonAlloueTab = file($motsCorpusAlloueFichier,
             FILE_IGNORE_NEW_LINES);
        $utiliseMotCorpusAlloue = true;
    }

    //
    //ouvrir connexion sur le fichier corpusPurifieStopList cree precedemment
    $corpusPurifieStopListFlux = fopen($corpusPurifieStopListFichier,"r");
    $corpusPurifieFlux = fopen($corpusPurifieFichier,"w");

    //$compNbrePhraseCorpus = 0;
    while(!feof($corpusPurifieStopListFlux))//nbrePhrase lignes //while ($compNbrePhraseCorpus < $nbrePhraseCorpusStopList)
    {
        //copmte le nombre de phrase de corpus
        //$compNbrePhraseCorpus++;

        $phraseCorpusStopList = fgets($corpusPurifieStopListFlux);
        
        $mettreAntiSlashT = "n";
        if(!feof($corpusPurifieStopListFlux))
        {
            $ligneCorpusPurifie = "";

            $phraseCorpusStopList = rtrim($phraseCorpusStopList);//eneleve \n
            $phraseCorpusStopListTab = explode("\t", $phraseCorpusStopList);
            
            foreach ($phraseCorpusStopListTab as $motPhraseCorpusStopList)
            {
                if(in_array($motPhraseCorpusStopList, $motsCorpusAlloueOuNonAlloueTab) ==
                        $utiliseMotCorpusAlloue)
                {
                    if($mettreAntiSlashT == "o")
                    {
                        $ligneCorpusPurifie .= "\t";
                    }
                    $mettreAntiSlashT = "o";

                    //creation de corpusPurifieStopList
                    $ligneCorpusPurifie .= $motPhraseCorpusStopList;

                }
            }

            //creation de corpusPurifieStopList
            if(!empty($ligneCorpusPurifie))//si $phraseCorpusStopListTab contienet que de $motsCorpusNonAlloue
            {
                $ligneCorpusPurifie .= "\n";
                fwrite($corpusPurifieFlux,$ligneCorpusPurifie);
                $nbrePhraseCorpusPurifie++;
            }
        }
    }

    //fermer connexion sur le fichier corpus purifie
    fclose($corpusPurifieFlux);
    fclose($corpusPurifieStopListFlux);
    //
    //ajouter les nombres des phrases dans le fichier nbreOccCorpus
    $nbrePhraseCorpusPurifieLigne = "Nombre des phrases du corpus purifiée\t".
        $nbrePhraseCorpusPurifie."\n" ;
    file_put_contents($constantesConfigurationFichier,$nbrePhraseCorpusPurifieLigne,FILE_APPEND);
    //

//    //supprimer les mots qui se trouve dans tout les phrases depuis $corpusPurifieFlux:
//    $nbreOccCorpusTab = file($constantesConfigurationFichier,FILE_IGNORE_NEW_LINES);
//    foreach($nbreOccCorpusTab as $motCorpus=>$nbreOccMotCorpus)
//    {
//        if($nbreOccMotCorpus == $nbrePhraseCorpusPurifie)
//        {
//            //enleve le mot depuis le corpus purifiée
//
//
//            //enlever le mot depuis le fichier des mots allouée et l'ajouter dans fichier
//            //des mots non allouée
//
//
//        }
//
//    }

    return $nbrePhraseCorpusPurifie;
}

?>
