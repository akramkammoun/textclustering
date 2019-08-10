<?php

//error_reporting(E_ALL);
//error_reporting(E_NOTICE);
//ini_set("display_errors", 1);

require ("includes/fonctions.php");

//if($_SERVER["SERVER_ADDR"] != "127.0.0.1" && $_SERVER["SERVER_ADDR"] != "127.0.1.1")
//    die("Acces refuser\nSi vous etes l'admin de serveur veuillez modifier le code de cette page pour reparer ce probleme");

if(isset($_GET["valider"])) :

$msgOk = "<center>Veuillez supprimer le script 'install.php' ou le mettre dans une zone
    unexecutable du serveur pour securite.</center><br /><br />";
$msgErreur = "";
//chemin R
$cmdR = isset($_GET["cmdR"])?fromGPC($_GET["cmdR"]):"";

//chemin Rar
$cmdRar = isset($_GET["cmdRar"])?fromGPC($_GET["cmdRar"]):"";

//nbre de Test de Kmeans
$nbreTestKmeans = isset($_GET["nbreTestKmeans"])?fromGPC($_GET["nbreTestKmeans"]):"";

//verification formulaire
if(empty($cmdR))
    $msgErreur .= "Veuillez entrez le chemin de programme R .<br />";
if(empty($cmdRar) || preg_match("#.exe$#i", $cmdRar))
    $msgErreur .= "Veuillez entrez le chemin de programme 7-zip sans extension .exe .<br />";
if(!preg_match("#^[1-9]$#",$nbreTestKmeans))
    $msgErreur .= "Veuillez entrez pour le nombre de test de Kmeans un entier(>=1 et <=9) .<br />";

//en cas d'erreur
if(!empty($msgErreur))
{
    $titreErreur = "Installation echouee";
    $imageErreurChemin = "images/symbol-error.png";
    echo msgCadreErreur($titreErreur,$msgErreur,$imageErreurChemin);
    exit;
}

//chemin projet
$phpProjetChemin = dirname($_SERVER["SCRIPT_FILENAME"]);

//programme de suppression
$cmdSupp = "";

//manipulation pour configuration entre OS
//if(stripos($_SERVER["SERVER_SOFTWARE"],"win"))//pour windows
if(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
{
    $sepOS = "\\\\";

    $phpProjetChemin = str_replace("/", $sepOS, $phpProjetChemin);

    $cmdR = str_replace("\\", "/", $cmdR);
    //
    //pour R    
    $cmdR = "cd ".dirname($cmdR)."\\ && ".basename($cmdR);
    $cmdR = str_replace("/", "\\", $cmdR);
    //cd C:\Program Files\R\R-2.10.1\bin\ & R.exe
    //
    $cmdRar = "cd ".dirname($cmdRar)."\\ && ".basename($cmdRar);
    $cmdRar = str_replace("/", "\\", $cmdRar);

    $cmdSupp = "rmdir /S /Q";
}
else //pour linux et autres
{
    $sepOS = "/";
    $cmdSupp = "rm -r";
}
$msgOk .= "Le serveur tourne sous ".PHP_OS." .<br />";
$msgOk .= "Le seprateur de chemin que votre systeme d'exploitation l'utilse est : $sepOS .<br />";
$msgOk .= "Le chemin de projet est : $phpProjetChemin .<br />";
$msgOk .= "La commande de programme 7-zip est : $cmdRar .<br />";
$msgOk .= "La commande de programme R est : $cmdR .<br />";
$msgOk .= "La commande de suppression est : $cmdSupp .<br />";
$msgOk .= "Le nombre de test de Kmeans est : $nbreTestKmeans .<br />";
$msgOk .= "<br /><center>Vous pouvez mettre a jour ces configrations a partir du fichier
 $phpProjetChemin{$sepOS}etc{$sepOS}conf.txt .</center><br />";

//enregistrer dans fichier conf
$etc = "sep_os = $sepOS\nchemin_projet = $phpProjetChemin\ncmd_r = $cmdR\ncmd_7-zip = $cmdRar\ncmd_supp = $cmdSupp\n".
        "nbre_test_kmeans = $nbreTestKmeans\n";
file_put_contents("etc/conf.txt", $etc);


$titreOk = "Installation accomplie";
$imageOkChemin = "images/symbol-ok.png";
echo msgCadreOk($titreOk,$msgOk,$imageOkChemin);

//header("Location: demarrage_projet.php");
else :
?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <script  type="text/javascript" src="js/form.js"></script>
    <link rel="stylesheet" type="text/css" href="css/mint_green.css" />
    <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
    <title>Installation</title>
</head>
    <body>
        <form method="GET" action="">
            <table class="tableCss">
                <tr>
                    <td colspan="2">Remplissez ces champs :</td>
                </tr>
                <tr>
                    <td>Chemin du programme R :</td>
                    <td><input type="text" name="cmdR" size="50"/></td>
                </tr>
                <tr>
                    <td>Chemin du programme 7-Zip :</td>
                    <td><input type="text" name="cmdRar" size="50"/>(sans l'extension .exe)</td>
                </tr>
                <tr>
                    <td>Nombre des tests de k-means</td>
                    <td><input type="text" name="nbreTestKmeans" />(min=1 et max=9)</td>
                </tr>
            </table>
            <input type="submit" name="valider" value="valider" />
        </form>
        <br />

        Exemples de chemin du programme R: <br />
        C:\Program Files\R\R-2.10.1\bin\R.exe <br />
        /usr/bin/R <br /><br />
        Exemples de chemin du programme 7-Zip : <br />
        C:\Program Files\7-Zip\7z <br />
        /usr/bin/7zr <br /><br />

    </body>
</html>

<?php

endif;
?>
