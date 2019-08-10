<?php

function triImmSomme($nbreMotsCorpusAlloue, $matriceImm, $matriceCorrelation, $valeursPropre, $nbreContext, $immFichierTrie) {
    for ($j = 1; $j < $nbreMotsCorpusAlloue + 1; $j++) { //initialisation j=1 car la premier colone contient les mots
        $somme[$j] = 0;
    }
    for ($i = 1; $i < $nbreMotsCorpusAlloue + 1; $i++) {
        for ($j = 1; $j < $nbreMotsCorpusAlloue + 1; $j++) {
            $valeursPropre[$j] = rtrim($valeursPropre[$j]);
            if ($valeursPropre[$j] > 1) {
                $somme[$i] = $somme[$i] + abs($matriceCorrelation[$i][$j]); //valeur absolue
            }
        }
    }
    //somme
    $matImmFlux = fopen($immFichierTrie, "w");
    arsort($somme);
    for ($i = 0; $i < $nbreMotsCorpusAlloue + 1; $i++) {
        $ch = "";
        foreach ($somme as $id => $nb) {
            if (!empty($ch)) {
                $ch1 = "";
                for ($compNbreContext = 0; $compNbreContext < $nbreContext; $compNbreContext++)
                    $ch1 .= "\t" . trim($matriceImm[$i][$nbreContext * ($id - 1) + $compNbreContext]);

                $ch .= $ch1;
            }
            else {
                $ch1 = "";
                $mettreAntiSlashT = "n";
                for ($compNbreContext = 0; $compNbreContext < $nbreContext; $compNbreContext++) {
                    if ($mettreAntiSlashT == "o")
                        $ch1 .= "\t";
                    $mettreAntiSlashT = "o";

                    $ch1 .= trim($matriceImm[$i][$nbreContext * ($id - 1) + $compNbreContext]);
                }


                $ch .= $ch1;
            }
        }
        $ch.= "\n";
        fwrite($matImmFlux, $ch);
    }
    fclose($matImmFlux);
    return $nbreMotsCorpusAlloue;
}

function triImmMax($nbreMotsCorpusAlloue, $matriceImm, $matriceCorrelation, $valeursPropre, $nbreContext, $immFichierTrie) {
    for ($j = 1; $j < $nbreMotsCorpusAlloue + 1; $j++) { //initialisation j=1 car la premier colone contient les mots
        $max[$j] = 0;
    }
    for ($i = 1; $i < $nbreMotsCorpusAlloue + 1; $i++) {
        for ($j = 1; $j < $nbreMotsCorpusAlloue + 1; $j++) {
            $valeursPropre[$j] = rtrim($valeursPropre[$j]);
            if ($valeursPropre[$j] > 1) {
                if (abs($matriceCorrelation[$i][$j]) > $max[$i])
                    $max[$i] = abs($matriceCorrelation[$i][$j]);
            }
        }
    }
    //somme
    $matImmFlux = fopen($immFichierTrie, "w");
    arsort($max);
    for ($i = 0; $i < $nbreMotsCorpusAlloue + 1; $i++) {
        $ch = "";
        foreach ($max as $id => $nb) {
            if (!empty($ch)) {
                $ch1 = "";
                for ($compNbreContext = 0; $compNbreContext < $nbreContext; $compNbreContext++)
                    $ch1 .= "\t" . trim($matriceImm[$i][$nbreContext * ($id - 1) + $compNbreContext]);

                $ch .= $ch1;
            } else {
                $ch1 = "";
                $mettreAntiSlashT = "n";
                for ($compNbreContext = 0; $compNbreContext < $nbreContext; $compNbreContext++) {
                    if ($mettreAntiSlashT == "o")
                        $ch1 .= "\t";
                    $mettreAntiSlashT = "o";

                    $ch1 .= trim($matriceImm[$i][$nbreContext * ($id - 1) + $compNbreContext]);
                }

                $ch .= $ch1;
            }
        }
        $ch.="\n";
        fwrite($matImmFlux, $ch);
    }
    fclose($matImmFlux);
}

function triImmMotAxe($nbreMotsCorpusAlloue, $matriceImm, $matriceCorrelation, $valeursPropre, $nbreContext, $immFichierTrie) {
    for ($j = 1; $j < $nbreMotsCorpusAlloue; $j++)
        $motAxe[0][$j] = 0;
    for ($i = 1; $i < $nbreMotsCorpusAlloue + 1; $i++) {
        for ($j = 1; $j < $nbreMotsCorpusAlloue + 1; $j++) {
            if ($valeursPropre[$i] > 1) {
                if (abs($matriceCorrelation[$j][$i]) > $motAxe[0][$i]) {
                    $motAxe[0][$i] = abs($matriceCorrelation[$j][$i]);
                    $motAxe[1][$i] = $j;
                }
            }
        }
    }
    $matImmFlux = fopen($immFichierTrie, "w");
    for ($i = 0; $i < $nbreMotsCorpusAlloue + 1; $i++) {
        $ch = "";
        $motUnique = array_unique($motAxe[1]);
        foreach ($motUnique as $nb) {
            if (!empty($ch)) {
                $ch1 = "";
                for ($compNbreContext = 0; $compNbreContext < $nbreContext; $compNbreContext++)
                    $ch1 .= "\t" . trim($matriceImm[$i][$nbreContext * ($nb - 1) + $compNbreContext]);

                $ch .= $ch1;
            } else {
                $ch1 = "";
                $mettreAntiSlashT = "n";
                for ($compNbreContext = 0; $compNbreContext < $nbreContext; $compNbreContext++) {
                    if ($mettreAntiSlashT == "o")
                        $ch1 .= "\t";
                    $mettreAntiSlashT = "o";

                    $ch1 .= trim($matriceImm[$i][$nbreContext * ($nb - 1) + $compNbreContext]);
                }

                $ch .= $ch1;
            }
        }
        $ch.="\n";
        fwrite($matImmFlux, $ch);
    }
    fclose($matImmFlux);
    $nbreMotsCorpusAlloueAxe = sizeof($motUnique);
    //fin tri mot axe
    return $nbreMotsCorpusAlloueAxe;
}
?>
