<?php

//compter le nombre de concepts expert

function nbreConceptsExpert($conceptsExpertFichier)
{
    if(!file_exists($conceptsExpertFichier))
        die("Erreur dans la fonction nbreConceptsExpert, la source de probleme
            est matriel. Veuillez recommencer.");

    $nbreConceptsExpert = 0;
    $conceptsExpertFlux = fopen($conceptsExpertFichier, "r");
    while(!feof($conceptsExpertFlux))
    {
        fgets($conceptsExpertFlux);
        if(!feof($conceptsExpertFlux))
        {
            $nbreConceptsExpert++;
        }
    }
    return $nbreConceptsExpert;
    fclose($conceptsExpertFlux);
}
?>
