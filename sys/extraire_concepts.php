<?php
function extraireConcepts($fichierClassesR,$nbreMotsAlloue,$nbreConceptsExpert,$fichierClasses)
{
    if(!file_exists($fichierClassesR))
    {
        die("Erreur dans R induit un probleme de creation de concepts
            dans la fonction extraireConcepts,
            la source de probleme est matriel. Veuillez recommencer.");
    }

    $fichierClassesRFlux=fopen($fichierClassesR,"r");
    $i=0;
    while(!feof($fichierClassesRFlux))
    {
        $content=rtrim(fgets($fichierClassesRFlux));
        if(!feof($fichierClassesRFlux))
        {
            $vect=explode("\t",$content);
            $num[$i]=$vect[0];

            //if(isset($vect[1]))
            $mot[$i]=$vect[1];
            $i++;
        }
    }
    fclose($fichierClassesRFlux);
    
    $fichierClassesFlux=fopen($fichierClasses,"w");
    for($j=1;$j<$nbreConceptsExpert+1;$j++)
    {
        $ch="";
        for($k=0;$k<$nbreMotsAlloue;$k++)
        {
            if($num[$k]==$j)
                if(!empty($ch))
                    $ch = $ch.",".trim($mot[$k]);
                else
                    $ch = $ch.trim($mot[$k]);
        }
        fputs($fichierClassesFlux,$ch."\n");
    }
    fclose($fichierClassesFlux);
}

?>
