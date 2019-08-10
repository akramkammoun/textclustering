<?php
function deleleEncodingMQ_GPC($chaine)
{
    //pour les valeurs qui provient de gpc
    //si magic quotes est active,retourner chaine apres suppression de l'encodage(=>appel a stripslashes())
    //si non retourner la chaine de debut
    return (get_magic_quotes_gpc())?stripslashes($chaine):$chaine;
}
function fromGPC($chaine)
{
    //enlever les espaces de debut et de fin
    return deleleEncodingMQ_GPC(trim($chaine));
}
function uploadFichierText($nomFichier,$cheminDest,&$message)
{
    //cette fonction ne verifie pas si la formuaire est envoyé pour l'affichage ou pour le traitement
    $message = "";
    if(isset($_FILES[$nomFichier]))
        $informations = $_FILES[$nomFichier];
    else
        return FALSE;
    $nom = fromGPC($informations["name"]);
    $typeMime = $informations["type"];
    $taille = $informations["size"];
    $fichierTemporaire = $informations["tmp_name"];
    $codeErreur = $informations["error"];
    switch($codeErreur)
    {
      case UPLOAD_ERR_OK:
            //fichie bien recue
            //copie le fichier temporaire
            //$cheminDest .= "/$nom";
            if($typeMime == "text/plain")
            {
                if(@copy($fichierTemporaire,$cheminDest))
                {
                    $message = "Transfert terminé - Fichier = $nom\n";
                    $message .= "Taille = $taille octects\n";
                    $message .= "Type MIME = $typeMime .";
                    return TRUE;
                }
                else
                {
                    $message = "Probleme de copie sur le serveur.";
                    return FALSE;
                }
            }
            else
            {
                $message = "Le fichier que vous avez uploader n'est pas un text, veuillez recommencer l'upload.";
                return FALSE;
            }
      break;
      case UPLOAD_ERR_NO_FILE:
            //pas de fichier saisie
            $message = "Pas de fichier saisie";
            return FALSE;
      break;
      case UPLOAD_ERR_INI_SIZE:
            $message = "Fichier $nom non transferé ";
            $message.= "(Taille > UPLOAD_MAX_FILESIZE).";
            return FALSE;
      break;
      case UPLOAD_ERR_FORM_SIZE:
            $message = "Fichier $nom non transferé ";
            $message.= "(Taille > MAX_FILE_SIZE).\n";
            return FALSE;
      break;
      case UPLOAD_ERR_PARTIAL:
            $message = "Fichier $nom non transferé ";
            $message.= "(Probleme lors de transfert).";
            return FALSE;
      break;
      case 5:
            $message = "Fichier $nom non transferé ";
            $message.= "(non trouvé).\n";
            return FALSE;
      break;
      default:
            $message = "Fichier $nom non transferé ";
            $message .= "(Erreur inconnue : $codeErreur).";
            return FALSE;
      break;
    }
}
function downloadFichier($nomFichier)
{
//c'est 2 headers propose au navigateur de traiter le fichier comme piece jointe
$header = "Content-Disposition: attachment; ";
$header .="filename=$nomFichier\n";
header($header);
header("Content-Type: application/x-rar-compressed\n");// x/y signifie que le navigateur accepte les MIME
set_magic_quotes_runtime(0);
readfile($nomFichier);
// a faire attention: il faut utiliser cette fonction au debut de la page car on va envoyer des header
}
function renommer($nom)
{
    //si le nom de dossier = *_v2 donc on va le renommer *_v3
    //si egale a * donc on le renomme *_v2
    if(preg_match("#_v\d*$#", $nom))
        $nom = substr($nom,0,strlen($nom)-1) . ((int)$nom[strlen($nom)-1] + 1);
    else
        $nom .= "_v2";
    return $nom;
}
function testAlphaNumerique($val)
{
    if(preg_match("#^\w+$#i", $val))
        return TRUE;
    else
        return FALSE;
}
function testNumeriqueNoNull($val)
{
    if(preg_match("#^([0-9])*$#", $val))
        return TRUE;
    else
        return FALSE;
}
function fichierVersMatrice($fichier,$sep)
{
    if(!file_exists($fichier))
        die("Erreur dans la fonction fichierVersMatrice, la source de probleme
            est matriel. Veuillez recommencer.");

    $fichierFlux = fopen($fichier,"r");
    $matrice = array();
    while(!feof($fichierFlux))
    {
        $content = rtrim(fgets($fichierFlux));
        if(!feof($fichierFlux))
        {
            $matrice[] = explode($sep,$content);
        }
    }
    fclose($fichierFlux);
    return $matrice;
}
function fichierVersTableau($fichier)
{
    if(!file_exists($fichier))
        die("Erreur dans la fonction fichierVersTableau, la source de probleme
            est matriel. Veuillez recommencer.");

    $fichierFlux = fopen($fichier,"r");
    $tableau = array();
    while(!feof($fichierFlux))
    {
        $content = rtrim(fgets($fichierFlux));
        if(!feof($fichierFlux))
        {         
            $tableau[] = $content;
        }
    }
    return $tableau;
}
function construireDelimiteur($delimteurChaine)
{
    $delimiteur = str_replace(array("\\n","\\r","\\t"),array("\n","\r","\t"),$delimteurChaine);

    return $delimiteur;
}

function msgCadreErreur($titre,$msg,$imageChemain)
{
    $cadre =  "<table align=\"left\" border=\"1\">
    <tr><td colspan=\"2\" bgcolor=\"RED\"><center><font color=\"WHITE\" size=\"6\">$titre</font></center></td></tr>
    <tr>
        <td style=\"border-right-width: 0\">$msg</td>
        <td style=\"border-left-width: 0\"><img src=\"$imageChemain\"</td>
    </tr>" ;

    return $cadre;
}
function msgCadreOk($titre,$msg,$imageChemain)
{
    $cadre =  "<table align=\"left\" border=\"1\">
    <tr><td colspan=\"2\" bgcolor=\"GREEN\"><center><font color=\"WHITE\" size=\"6\">$titre</font></center></td></tr>
    <tr>
        <td style=\"border-right-width: 0\">$msg</td>
        <td style=\"border-left-width: 0\"><img src=\"$imageChemain\"</td>
    </tr>" ;

    return $cadre;
}
?>
