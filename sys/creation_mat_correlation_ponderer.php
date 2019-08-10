<?php
function creationMatriceCorrelationPonderer($nbreMotsCorpusAlloue,$matriceCorrelation,$valeursPropre)
{
    for($i=1;$i<$nbreMotsCorpusAlloue+1;$i++)
        for($j=1;$j<$nbreMotsCorpusAlloue+1;$j++)
            $matriceCorrelationPonderer[$i][$j]=abs($matriceCorrelation[$i][$j])*$valeursPropre[$j];
        
    return $matriceCorrelationPonderer;
}
?>
