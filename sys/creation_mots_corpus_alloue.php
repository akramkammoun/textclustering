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
function creationMotsCorpusAlloue($tauxNbreMotsAlloueCorpusMin,$tauxNbreMotsAlloueCorpusMax,
        $corpusFichier,$stopListFichier,$delemiteurPhraseCorpus,$delimiteurFinPhraseCorpus,
        $constantesConfigurationFichier,$nbreOccCorpusFichier,$motsCorpusAlloueFichier,
        $motCorpusNonAlloueFichier,$corpusPurifieStopListFichier)
{

    //variables de retour:
    $nbreMotsCorpusAlloue = 0;
	$nbrePhrasesCorpus=0;

    //variables intermidiaires
    $nbreMotsCorpus = 0;
    $nbreOccMotsCorpusTab = array();//mot=>nbreOcc
 
    //$nbreMotsAlloueMin = 0;
    //$motsCorpusNonAlloue = 0;

    //**********travail*************

    //supprimer les phrases redondante

    //enleve le delimiteur de fin utilisateur puis met le mot stop list en majuscule
    //et mets tout ces mots dans un tableau.
    $stopListTab = fileRtrimMaj($stopListFichier);

    if(!file_exists($corpusFichier))
        die("Erreur dans la fonction creationMotsCorpusAlloue, la source de probleme
            est matriel. Veuillez recommencer.");

    //ouvrir connexion sur le fichier corpus
    $corpusFlux = fopen($corpusFichier, "r");

    //ouvrir connexion sur le fichier corpusPurifieStopList
    $corpusPurifieStopListFlux = fopen($corpusPurifieStopListFichier,"w");

    while (!feof($corpusFlux))//nbrePhrase lignes
    {
        //lire la phrase
        $phraseCorpus = fgets($corpusFlux);
        //enleve le delimiteur choisie par l'utilisateur

        //ligne a inserer dans corpus Stop list s'il nest pas vide cad apres les trims exposé pour la phrase
        //la phrase n'est pas vide a la fin.
        $ligneCorpusStopList = "";

        if(!empty($phraseCorpus))//si phrase n'est pas vide (corpus peut avoir des lignes vides)
        {
            //compter le nombre de phrases non vide pour calculer le nombre min et max de mots alloué.
            $nbrePhrasesCorpus++;
            
            $phraseCorpus = rtrim($phraseCorpus,$delimiteurFinPhraseCorpus."\n");
            //enlever les carateres unitile qui encerlce la phrase
            $phraseCorpus = trim($phraseCorpus);
            
            $phraseCorpusTab = explode($delemiteurPhraseCorpus, $phraseCorpus);

            //enleve les mots redondantes dans la meme phrase
            $phraseCorpusTab = array_unique($phraseCorpusTab);

            $mettreAntiSlashT = "n";

            foreach ($phraseCorpusTab as $motPhraseCorpus)
            {
                $motPhraseCorpus = trim($motPhraseCorpus);

                if(!empty($motPhraseCorpus))//si il y a 2 ou plusieurs qui se succedent
                {
                    //majuscule de mot de corpus
                    $motPhraseCorpus = strtoupper($motPhraseCorpus);
                    

                    if(!in_array($motPhraseCorpus, $stopListTab))
                    {
                        if($mettreAntiSlashT == "o")
                        {
                            $ligneCorpusStopList .= "\t";
                        }
                        $mettreAntiSlashT = "o";

                        //creation de corpusPurifieStopList
                        $ligneCorpusStopList .= $motPhraseCorpus;

                        //creation de nbreOccMotsCorpus
                        //
                        //si le motAlloue se trouve dans $nbreOccMotsCorpusTab cad qu'il a ete deja inscris dans le tableau
                        //donc on va incrementer la valeur de nbreOccurence par 1.
                        if(array_key_exists($motPhraseCorpus, $nbreOccMotsCorpusTab))
                            $nbreOccMotsCorpusTab[$motPhraseCorpus]++;
                        else
                            $nbreOccMotsCorpusTab[$motPhraseCorpus] = 1;

                    }
                }
            }

            //creation de corpusPurifieStopList
            if(!empty($ligneCorpusStopList))//si $phraseCorpusS contient que de $stopListTab
            {   
                $ligneCorpusStopList .= "\n";
                fwrite($corpusPurifieStopListFlux,$ligneCorpusStopList);
            }
        }
    }

    //fermer connexion sur le fichier corpus purifie
    fclose($corpusPurifieStopListFlux);

    //fermer connexion sur le fichier corpus
    fclose($corpusFlux);

    //compter le nombre de $nbreOccMotsCorpusTab;
    $nbreMotsCorpus = count($nbreOccMotsCorpusTab);

    //on supprime $stopListTab, on a pas besoin
    unset($stopListTab);


    $nbreMotsAlloueMin = round($nbrePhrasesCorpus*$tauxNbreMotsAlloueCorpusMin);
    //
    $nbreMotsAlloueMax = round($nbrePhrasesCorpus*$tauxNbreMotsAlloueCorpusMax);
    //
    //ecrire les motsCorupsStopList avec leurs occurences dans un fichier et le min nombre occurences
    //et trouver les mots alloue
    $nbreOccCorpusFlux = fopen($nbreOccCorpusFichier,"w");
    $motsCorpusAlloueFlux = fopen($motsCorpusAlloueFichier,"w");
    $motCorpusNonAlloueFlux = fopen($motCorpusNonAlloueFichier,"w");
     //trouve nbreMinMotsAlloue

    foreach($nbreOccMotsCorpusTab as $motCorpus => $nbreOccMotCorpus)
    {
        $ligne = $motCorpus."\t".$nbreOccMotCorpus."\n";
        fwrite($nbreOccCorpusFlux, $ligne);

        //trouver les mots alloue
        if($nbreOccMotCorpus >= $nbreMotsAlloueMin && $nbreOccMotCorpus <= $nbreMotsAlloueMax)
        {
            $nbreMotsCorpusAlloue++;
            fwrite($motsCorpusAlloueFlux, $motCorpus."\n");
        }
        else//trouver les mots non alloue
            fwrite($motCorpusNonAlloueFlux, $motCorpus."\n");
    }
    fclose($motCorpusNonAlloueFlux);
    fclose($motsCorpusAlloueFlux);
    fclose($nbreOccCorpusFlux);
    //

    $constantesConfigurationFlux = fopen($constantesConfigurationFichier,"a+");
    $ligne  = "Nombre des phrases de corpus donnee\t{$nbrePhrasesCorpus}\n";
    $ligne .= "Nombre total des mots de corpus\t{$nbreMotsCorpus}\n";
    $ligne .= "Nombre des mots allouee du corpus\t{$nbreMotsCorpusAlloue}\n";
    $ligne .= "Nombre minimum d'occurence des mots alloues\t$nbreMotsAlloueMin\n";
    $ligne .= "Nombre maximum d'occurence des mots alloues\t$nbreMotsAlloueMax\n";
    fwrite($constantesConfigurationFlux,$ligne);
    fclose($constantesConfigurationFlux);

    return $nbreMotsCorpusAlloue;
}
    //fonctions a utilise


function fileRtrimMaj($nomFichier)
{
    if(!file_exists($nomFichier))
        die("Erreur dans la fonction fileRtrimMaj dans la fonction creationMotsCorpusAlloue, la source de probleme
            est matriel. Veuillez recommencer.");

    $fichierTab = array();

    $fichierFlux = fopen($nomFichier, "r");
    while (!feof($fichierFlux))
    {
        //enelve les \n de droit puis majuscule .
        $fichierTab[] = strtoupper(rtrim(fgets($fichierFlux)));
    }
    fclose($fichierFlux);

    return $fichierTab;
}
?>
