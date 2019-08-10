<?php
/*
on caclul les deux matrice $mat_nobar_nobar et $mat_nobar_bar qui sont les plus facile a extraire et
a partir de ces deux matrice on peut de monter les deux autres:
    * $mat_bar_nobar: transpose de $mat_nobar_bar
    * $mat_bar_bar : nbrePhrase - (mat_nobar_nobar  + mat_nobar_bar + mat_bar_nobar)

*on commence par extraire le mot1 qui se trouve dans le corpus car la matrice comporte des 1 moins
que les 0 donx la recherche est plus vite

*puis apres fixé m1 = 1(touvé), il faut chercher pour m2=0 et m2=1, on commence par chercher m2=0(premier if)
car les 0 sont beaucoup plus que les 1, puis on chercher m2=1(else)

*pour trouve les nbre d'occurence des mots dans le diagonale de la matrice mat_nobar_nobar .

*on applique imm avec le nouveau equation imm developpé

Rq: on initialise juste mat_nobar_nobar et mat_nobar_bar avec des 0 (nbreMot*nbreMot)

*/
function creationMatImm($corpusPurifieFichier,$motsCorpusAlloueFichier,$nbrePhrasesCorpusPurifie,$immFichier)
{
    
    //****travail
    $log10NbrePhraseX2 = log10($nbrePhrasesCorpusPurifie)*2;
    $unSurNbrePhrase = 1/$nbrePhrasesCorpusPurifie;

    //faire le trim des mots apres remplir les mots dans un tableau
    //Rq: on mait tout les mots dans un tableau est meilleur de le parcourir ces mots,
    //car on va parcourir le tableau plusieurs fois ce tableau.
    //FILE_IGNORE_NEW_LINES : pour supprimer \n apres chaque ligne.
    $motsCorpusAlloueTab = file($motsCorpusAlloueFichier,FILE_IGNORE_NEW_LINES);//chaines triee ou non
    $nbreMotsCorpusAlloue = count($motsCorpusAlloueTab);

    //initialisation de deux matices $mat_nobar_nobar et $mat_nobar_bar avec des 0
    for($i=0;$i<$nbreMotsCorpusAlloue;$i++)
        for($j=0;$j<$nbreMotsCorpusAlloue;$j++)
        {
            $mat_nobar_nobar[$i][$j] = 0;
            $mat_nobar_bar[$i][$j] = 0;
            //$mat_bar_nobar[$i][$j] = 0;
            //$mat_bar_bar[$i][$j] = 0;
        }

    if(!file_exists($corpusPurifieFichier))
        die("Erreur dans la fonction creationMatImm, la source de probleme
            est matriel. Veuillez recommencer.");

    //ouvrir connexion sur le fichier corpus
    $corpusPurifieFlux = fopen($corpusPurifieFichier, "r");

    //calcul des matrices $mat_nobar_bar et $mat_nobar_nobar
    while (!feof($corpusPurifieFlux))//nbrePhrase lignes
    {
        //supprimer \n de la phrase purifie
        $phraseCorpusPurifie = rtrim(fgets($corpusPurifieFlux),"\n");
        if(!feof($corpusPurifieFlux))
        {
            $phraseCorpusPurifieTab = explode("\t", $phraseCorpusPurifie);

            for($i=0;$i<$nbreMotsCorpusAlloue;$i++)
            {
                if(in_array($motsCorpusAlloueTab[$i], $phraseCorpusPurifieTab))//m1=1
                {
                    for($j=0;$j<$nbreMotsCorpusAlloue;$j++)
                    {
                        if(!in_array($motsCorpusAlloueTab[$j], $phraseCorpusPurifieTab))//m2=0
                        {
                            $mat_nobar_bar[$i][$j] += 1;
                        }//m1=1 et m2=0
                        else//m2=1
                        {
                            $mat_nobar_nobar[$i][$j] += 1;
                        }//m1=1 et m2=1
                    }
                }
            }//fin for i
        }
    }
    //fermer connexion sur le fichier corpus
    fclose($corpusPurifieFlux);

    //creation de l'entete des mots de fichier imm
    $motsCorpusAlloueLigne = "";
    for($i=0;$i<$nbreMotsCorpusAlloue-1;$i++)
    {
        $motsCorpusAlloueLigne .= $motsCorpusAlloueTab[$i]."\t";
    }
    $motsCorpusAlloueLigne .= $motsCorpusAlloueTab[$nbreMotsCorpusAlloue-1]."\n";

    //ouvrir connexion de imm
    $immFlux = fopen($immFichier,"w");
    //ecrire l'entete
    fwrite($immFlux,$motsCorpusAlloueLigne);

    //calcul de imm
    for($j=0;$j<$nbreMotsCorpusAlloue;$j++)
    {
        //nombre d'occurence
        $nbreOccMot_nobar_j = $mat_nobar_nobar[$j][$j];//copie(diagonale de mat_1_1 represente le nombre d'occurence)
        $nbreOccMot_bar_j = $nbrePhrasesCorpusPurifie - $nbreOccMot_nobar_j;//calcule

        $immReq = "";
        $mettreAntiSlashT = "n";

        for($i=0;$i<$nbreMotsCorpusAlloue;$i++)
        {
            if($mettreAntiSlashT == "o")
            {
                $immReq .= "\t";
            }
            $mettreAntiSlashT = "o";

            //nombre d'occurence
            $nbreOccMot_nobar_i = $mat_nobar_nobar[$i][$i];//copie(diagonale de mat_1_1 represente le nombre d'occurence)
            $nbreOccMot_bar_i = $nbrePhrasesCorpusPurifie - $nbreOccMot_nobar_i;//calcule

            //calcule de 0_0 et 0_1
            //si valeur est null(case non remplie) alors il sera remplie par 0
            $val_nobar_nobar_i_j = $mat_nobar_nobar[$i][$j];//juste copie la valeur
            $val_nobar_bar_i_j = $mat_nobar_bar[$i][$j];//juste copie la valeur
            $valt_bar_nobar_i_j = $mat_nobar_bar[$j][$i];//calule la valeur par le transpose de mat_1_0
            $val_bar_bar_i_j = $nbrePhrasesCorpusPurifie - ($val_nobar_nobar_i_j+$val_nobar_bar_i_j+$valt_bar_nobar_i_j);//calcule

            //calcule la partie somme de imm
            $immPartie = 0;
            if($val_nobar_nobar_i_j != 0)
                $immPartie += $val_nobar_nobar_i_j*(log10($val_nobar_nobar_i_j/($nbreOccMot_nobar_i*carre($nbreOccMot_nobar_j))) + $log10NbrePhraseX2);
            if($val_nobar_bar_i_j != 0)
                $immPartie += $val_nobar_bar_i_j*(log10($val_nobar_bar_i_j/($nbreOccMot_nobar_i*carre($nbreOccMot_bar_j))) + $log10NbrePhraseX2);
            if($valt_bar_nobar_i_j != 0)
                $immPartie += $valt_bar_nobar_i_j*(log10($valt_bar_nobar_i_j/($nbreOccMot_bar_i*carre($nbreOccMot_nobar_j))) + $log10NbrePhraseX2);
            if($val_bar_bar_i_j != 0)
                $immPartie += $val_bar_bar_i_j*(log10($val_bar_bar_i_j/($nbreOccMot_bar_i*carre($nbreOccMot_bar_j))) + $log10NbrePhraseX2);

            //calcul imm d'une case
            $immReq .= $unSurNbrePhrase*($immPartie);
        }
        if($i==$j)
            $immReq .= 0;

        $immReq .= "\n";


        fwrite($immFlux,$immReq);
    }

    fclose($immFlux);
}

//fonction a utilisé
function carre($val)
{
    return $val*$val;
}
?>
