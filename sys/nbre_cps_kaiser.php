<?php
function nbreCpsKaiser($valeursPropreTab)
{
    $nombreComposantesKaiser = 0;
    $nombreComposantesTotal = count($valeursPropreTab);

    //On commence j=1 car la premier ligne n'est pas une valeur propre mais c'est juste un text simple
    for($j = 1;$j < $nombreComposantesTotal;$j++)
        if($valeursPropreTab[$j] > 1)
            $nombreComposantesKaiser++;
        
    return $nombreComposantesKaiser;
}

?>
