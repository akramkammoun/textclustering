<?php
function efficacite($fichierClasses,$expertConceptFichier,$nbreMotsAlloue)
{
    //il peut que l'utilisateur ne veut pas evouler la resultat donc il ne donne pas de concepts expert
    if(!file_exists($expertConceptFichier))
        return 0;

    $fic1=fopen($expertConceptFichier,"r");
    $i=0;
    while(!feof($fic1))
    {
        $content=rtrim(fgets($fic1));
        if(!feof($fic1))
        {
            $vect[$i]=explode(",",$content);
            $i++;
        }
    }
    fclose($fic1);
    $fic2=fopen($fichierClasses,"r");
    $j=0;
    $l=0;
    while(!feof($fic2))
    {
        $content=rtrim(fgets($fic2));
        $concept[$j]=explode(",",$content);
        $j++;
    }
    fclose($fic2);
    $ft=0;
    for($a=0;$a<$i-1;$a++)
    {
        $max=0;
        for($k=0;$k<$j;$k++)
        {
            $n=0;
            for($m=0;$m<sizeof($concept[$k]);$m++)
                for($b=0;$b<sizeof($vect[$a]);$b++)
                    if(!strcmp(trim($concept[$k][$m]),trim($vect[$a][$b])))
                        $n++;
            $pi=sizeof($vect[$a])/$nbreMotsAlloue;
            $r=$n/sizeof($vect[$a]);
            $p=$n/sizeof($concept[$k]);
            if(($r+$p)>0)
                $f=(2*$p*$r)/($r+$p);
            else
                $f=0;
            if($f>$max)
                $max=$f;

            $l=$l+$n;
        }

        $ft=$ft+($max*$pi);
    }

    $eff = $ft*100;
    return $eff;
}
?>
