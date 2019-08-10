<?php

function creationInterfaceGraphiqueImm($immTypeTab,$methodeTab,$typeMethodeTab)
{
    $voirDiv = "o";
    $formComplet ="";
    foreach($immTypeTab as $immTypeNom => $immType)
    {
        $idDiv = "{$immType}IdDiv";
        if($voirDiv == "o")
        {
            $formComplet .= "<fieldset><legend><b><input type=\"checkbox\" name=\"{$immType}[selecter]\"
            onchange=\"enableField('$idDiv');\" checked=\"checked\" />$immTypeNom</b></legend>
            <div id=\"$idDiv\" style=\"display:block\" >";
            //$typeImm="imm";
        }
        else
        {
            $formComplet .= "<fieldset><legend><b><input type=\"checkbox\" name=\"{$immType}[selecter]\"
            onchange=\"enableField('$idDiv');\" />$immTypeNom</b></legend>
            <div id=\"$idDiv\" style=\"display:none\" >";
            //$typeImm = "immContext";
        }

        $voirDiv = "n";

        $formComplet .= "<table class=\"tableCss\"><th>";

        foreach($methodeTab as $methodeName=>$methode)
        {
//            $formComplet .= "<td><input type=\"checkbox\" name=\"{$immType}[$methode][selecter]\" /></td>
//                    <td colspan=\"2\">$methodeName</td>";
//            $autreInfo = "";
//            //$methode==kmeans
//            if($methode == "kmeans")
//                $autreInfo .= "<input type=\"text\" name=\"{$immType}[kmeans][nbreTestKmeans]\" 
//                    value=\"$nbreTestKmeans\" size=\"1\" />";
                
                
            $formComplet .= "<td colspan=\"2\">$methodeName</td>";
        }

        //fin tr methode et debut tr typeMethode tri(min,marge)
        $formComplet .= "</tr><tr><td>methodes de tri</td>";

        $nbreMethode = count($methodeTab);

        $compNbreMethode = $nbreMethode;
        while($compNbreMethode != 0)
        {
//            $formComplet .= "<td></td><td>Min%</td><td>Marge</td>";
            $formComplet .= "<td>Min termes (%)</td><td>Marge</td>";
            $compNbreMethode--;
        }

        //fin tr typeMethode tri(min,marge) et debut typeMethode(case coche)
        $formComplet .= "</tr>";

        foreach($typeMethodeTab as $typeMethodeNom => $typeMethode)
        {
            $formComplet .= "<tr><td>$typeMethodeNom</td>";

            foreach ($methodeTab as $methode)
            {
//                $formComplet .= "<td><input type=\"checkbox\" name=\"{$immType}[$methode][$typeMethode][selecter]\" /></td>
//                 <td><input type=\"text\" name=\"{$immType}[$methode][$typeMethode][min]\" size=\"3\" /></td>
//                 <td><input type=\"text\" name=\"{$immType}[$methode][$typeMethode][marge]\" size=\"4\" /></td>";
                $formComplet .= "<td><input type=\"text\" name=\"{$immType}[$methode][$typeMethode][min]\" size=\"4\" /></td>
                                 <td><input type=\"text\" name=\"{$immType}[$methode][$typeMethode][marge]\" size=\"4\" /></td>";
            }
            $formComplet .= "</tr>";
        }
        $divVarMinNom = "{$idDiv}ToutMin";
        $divVarMargeNom = "{$idDiv}ToutMarge";
        $formComplet .= "
        </table><hr width=\"30%\" />
        <table align=\"center\">
        <th>
            <td>Min termes (%)</td><td>Marge</td>
        </th>
        <tr>
            <td><input type=\"button\" onClick=\"remplir('$idDiv','{$divVarMinNom}','{$divVarMargeNom}');\"
                value=\"Remplir tout par: \" >
            </td>
            <td><input type=\"text\" name=\"{$divVarMinNom}\" size=\"4\"></td>
            <td><input type=\"text\" name=\"{$divVarMargeNom}\" size=\"4\"></td>
        </tr>
        </table>
        </div></fieldset><br /><hr width=\"60%\" />";
    }
    
    return $formComplet;
}
?>