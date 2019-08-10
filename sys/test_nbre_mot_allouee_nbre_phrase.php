<?php

function TesterNbreMotAlloueesNbrePhrases($nbreOccCorpusFichier,$nbrePhraseCorpusPurifie,&$motInterdit)
{
    $motsCorpusAlloueTab = file($nbreOccCorpusFichier,FILE_IGNORE_NEW_LINES );

    //$motInterdit : ce sont les termes qui se voient dans toutes les phrases du corpus
    $motInterdit = "<table border=1><tr><td>termes</td><td>nombres d'occurences</td></tr>";
    $trouveMotInterdit = false;

    foreach ($motsCorpusAlloueTab as $motCorpusAlloue)
    {
        //on enleve \n de toutes les lignes
        //$motCorpusAlloue = trim($motCorpusAlloue, "\n");

        $motCorpusAlloueTab = explode("\t", $motCorpusAlloue);
        //$motCorpusAlloueTab[0] = mot ; $motCorpusAlloueTab[1] : nbreOccurence

        if($motCorpusAlloueTab[1] == $nbrePhraseCorpusPurifie)
        {
            $trouveMotInterdit = true;
            $motInterdit .= "<tr><td>".$motCorpusAlloueTab[0]."</td><td>".
                $motCorpusAlloueTab[1]."</td></tr>";
        }
    }

    $motInterdit .= "</table>";
    return $trouveMotInterdit;
}


?>
