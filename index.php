<?php

//error_reporting(E_ALL);
//error_reporting(E_NOTICE);

require ("sys/creation_mots_corpus_alloue.php");
require ("sys/creation_corpus_purifie.php");
require ("sys/creation_mat_doc_terme.php");
require ("sys/creation_mat_imm.php");
require ("sys/tri_imm.php");
require ("sys/classification_imm.php");
    require ("sys/efficacite_concepts.php");//liée que a sys/classification_imm.php
    require ("sys/extraire_concepts.php");//liée que a sys/classification_imm.php
require ("sys/nbre_concepts_expert.php");
require ("sys/creation_mat_imm_context.php");
require ("sys/creation_fichier_r.php");
require ("sys/interface_graphique_imm.php");
require ("sys/creation_mat_correlation_ponderer.php");
require ("sys/nbre_cps_kaiser.php");
require ("sys/test_nbre_mot_allouee_nbre_phrase.php");

require ("includes/fonctions.php");
//require ("includes/msg_cadre.php");





//************* debut configuration projet **************//
//
$confLignes = @file("etc/conf.txt",FILE_IGNORE_NEW_LINES);
if($confLignes == NULL)
{
    $titreErreur = "Application non installee";
    $msgErreurForm = "Si vous etes l'admin, veuillez executer le script install.php pour l'installation du programme";
    $imageErreurChemain = "images/symbol-error.png";
    echo msgCadreErreur($titreErreur,$msgErreurForm,$imageErreurChemain);
    exit;

}
foreach ($confLignes as $confLigne)
{
    $conf = explode("=", $confLigne);
    $confProjetTab[trim($conf[0])] = trim($conf[1]);
}
unset($confLignes);
//
$sepOS                   = isset($confProjetTab["sep_os"])?$confProjetTab["sep_os"]:"";// linux = \ ; windows = \\ ou \\\\
$phpProjetChemin         = isset($confProjetTab["chemin_projet"])?$confProjetTab["chemin_projet"]:"";
$cmdR                    = isset($confProjetTab["cmd_r"])?$confProjetTab["cmd_r"]:"";
$cmd7Zip                 = isset($confProjetTab["cmd_7-zip"])?$confProjetTab["cmd_7-zip"]:"";
$cmdSupp                 = isset($confProjetTab["cmd_supp"])?$confProjetTab["cmd_supp"]:"";
$nbreTestKmeans          = isset($confProjetTab["nbre_test_kmeans"])?$confProjetTab["nbre_test_kmeans"]:"";
//
if(empty($sepOS) || empty($phpProjetChemin) || empty($cmdR) || empty($cmd7Zip) || empty($cmdSupp) || empty($nbreTestKmeans))
{
    $titreErreur = "Erreur de configuration";
    $msgErreurForm = "Error de configuration, si vous etes l'admin, veuillez executer le script install.php pour
        corriger ce probleme";
    $imageErreurChemain = "images/symbol-error.png";
    echo msgCadreErreur($titreErreur,$msgErreurForm,$imageErreurChemain);
    exit;

}
//
unset($confProjetTab);
//
//************ fin configuration projet ****************//





//*************** debut variable global *******************//
//
$immTypeTab = array("Imm"=>"immTab","Imm contextuelle"=>"immContextTab");

$methodeNomTab = array("CLARA"=>"clara", "PAM"=>"pam", "K-means"=>"kmeans", "Hclust"=>"hclust","Mclust"=>"mclust");
$typeMethodeNomTab = array("Sans tri"=>"sans_tri","Somme correlations"=>"cor_somme","Max correlations"=>"cor_max",
    "Somme correlations ponderees"=>"cor_ponderer_somme","Max correlations ponderees"=>"cor_ponderer_max",
    "Terme par composante principale"=>"mot_axe");
//
//*************** fin variable global *******************//





if(isset($_POST["executer"])) :

//************** debut programme ***************//
$tempsGlobaldebut = microtime(true);
//




//************* debut : creation de l'arborescence projet *******//
//
$msgErreurForm = "";
//
$nomProjet = isset($_POST["nomProjet"])?fromGPC($_POST["nomProjet"]):"";
if(!testAlphaNumerique($nomProjet))
    $msgErreurForm .= "Veuillez Donnez un nom de projet alpha numerique sans caracteres speciaux .<br />";
//
$mesProjetsDir = "$phpProjetChemin{$sepOS}mes_projets";
$projetDir = "$mesProjetsDir{$sepOS}$nomProjet";
$resultatDirNom = "resultats";
//
while(file_exists($projetDir))
{
    $nomProjet = renommer($nomProjet);
    $projetDir = "$mesProjetsDir{$sepOS}$nomProjet";
}
mkdir($projetDir,0777,TRUE);
//chemin des dossiers de projet
$usrDir = "$projetDir{$sepOS}usr";
$tmpDir = "$projetDir{$sepOS}tmp";
$logsDir = "$projetDir{$sepOS}logs";
$resultatsDir = "$projetDir{$sepOS}$resultatDirNom";
$rScriptsDir = "$tmpDir{$sepOS}r_scripts";
//creation dossiers de projet
mkdir($usrDir,0777,TRUE);
mkdir($tmpDir,0777,TRUE);
mkdir($logsDir,0777,TRUE);
mkdir($resultatsDir,0777,TRUE);
mkdir($rScriptsDir,0777,TRUE);
$logGlobalFichier = "$logsDir{$sepOS}log.txt";
file_put_contents($logGlobalFichier,"");//creer le fichier log
//
//************* fin : creation de l'arborescence projet *******//





//************* debut : scripts R dans le disque dur *******************//
//
$acpFichierDisque = "$phpProjetChemin{$sepOS}r_scripts{$sepOS}acp.r";
$ClassificationFichierDisque = "$phpProjetChemin{$sepOS}r_scripts{$sepOS}classification.r";
//$conceptsImmContextFichierDisque = "$phpProjetChemin{$sepOS}r_scripts{$sepOS}concepts_imm_context.r";
$fonctionConceptFichierDisque = "$phpProjetChemin{$sepOS}r_scripts{$sepOS}fonction_classification.r";
//
$fonctionConceptFichier = $rScriptsDir.$sepOS."fonction_concepts.r";
copy($fonctionConceptFichierDisque,$fonctionConceptFichier);
//
//************* fin : scripts R dans le disque dur *******************//




 
//************* debut : donnee par utilisateur ****************//
//
//nomProjet est situé en haut au debut necessairement
$corpusFichier =                  "$usrDir{$sepOS}corpus.txt";
$erreurUploadCorpus = uploadFichierText("corpus",$corpusFichier,$msgUploadCorpus);
//
$delimiteurFinPhraseCorpus =      (isset($_POST["delimFinPhrase"]))?fromGPC($_POST["delimFinPhrase"]):"";
$delemiteurPhraseCorpus =         (isset($_POST["delimEntrePhrase"]))?fromGPC($_POST["delimEntrePhrase"]):"";
//
//construire les delimiteurs
$delimiteurFinPhraseCorpus = construireDelimiteur($delimiteurFinPhraseCorpus);
$delemiteurPhraseCorpus = construireDelimiteur($delemiteurPhraseCorpus);
//
if(empty($delemiteurPhraseCorpus))
    $delemiteurPhraseCorpus = " ";
//
$tauxNbreMotsAlloueCorpusMin = (isset($_POST["tauxMin"]) && is_numeric($_POST["tauxMin"]) &&
        $_POST["tauxMin"] >= 0 && $_POST["tauxMin"] <= 100)?fromGPC($_POST["tauxMin"])/100:"";
if(!is_numeric($tauxNbreMotsAlloueCorpusMin))
    $tauxNbreMotsAlloueCorpusMin = 0;// (0/100)

$tauxNbreMotsAlloueCorpusMax = (isset($_POST["tauxMax"]) && is_numeric($_POST["tauxMax"]) &&
        $_POST["tauxMax"] >= 0 && $_POST["tauxMax"] <= 100)?fromGPC($_POST["tauxMax"])/100:"";
if(!is_numeric($tauxNbreMotsAlloueCorpusMax))
    $tauxNbreMotsAlloueCorpusMax = 1;// (100/100)

if($tauxNbreMotsAlloueCorpusMin > $tauxNbreMotsAlloueCorpusMax)
{
    $msgErreurForm .= "Veuiller donner un taux maximal des termes a considerer plus grand que
        le taux minimal des termes a considerer .<br>";
}
//
$conceptsExpertFichier =          "$usrDir{$sepOS}concepts_expert.txt";
$erreurUploadConceptsExpert = uploadFichierText("conceptsExpert",$conceptsExpertFichier,$msgUploadConceptsExpert);
//
$nbreConceptsExpert =     (isset($_POST["nbreConceptsExperts"]))?$_POST["nbreConceptsExperts"]:"";
if(!testNumeriqueNoNull($nbreConceptsExpert))
    $msgErreurForm .= "Veuiller donner un nombre de concepts (entier > 1) si non ne donner pas et
        specifier un concept expert .<br>";
//
if(empty($nbreConceptsExpert) && !$erreurUploadConceptsExpert)
    $msgErreurForm .= "concepts expert : $msgUploadConceptsExpert ou donner un nombre de concepts (entier > 1) .<br>";
//
$stopListFichier =                "$usrDir{$sepOS}stoplist.txt";
$erreurUploadStopList = uploadFichierText("stopList",$stopListFichier,$msgUploadStopList);
//
$immTab =                         (isset($_POST["immTab"]))?$_POST["immTab"]:array();
//
$immContextTab =                  (isset($_POST["immContextTab"]))?$_POST["immContextTab"]:array();
//
if(!$erreurUploadCorpus)
    $msgErreurForm .= "Corpus : $msgUploadCorpus .<br />";
//if(!$erreurUploadConceptsExpert)
//    $msgErreurForm .= "Concepts experts : ".$msgUploadConceptsExpert."<br />";
if(!$erreurUploadStopList)
    $msgErreurForm .= "Liste des termes a enlever : $msgUploadStopList .<br />";
//
//if(!is_numeric($immTab["kmeans"]["nbreTestKmeans"]))
//    $msgErreurForm .= "Le nombre de test de kmeans de IMM doit etre un entier. <br>";
//if(!is_numeric($immContextTab["kmeans"]["nbreTestKmeans"]))
//    $msgErreurForm .= "Le nombre de test de kmeans de IMM Contextuelle doit etre un entier. <br>";
//die($nbreTestKmeans . " - " . $immTab["kmeans"]["nbreTestKmeans"]);
//
$nbreTestKmeans = isset($_POST["nbreTestKmeans"])?fromGPC($_POST["nbreTestKmeans"]):"";
if(!preg_match("#^[1-9]$#",$nbreTestKmeans))
    $msgErreurForm .= "Veuillez entrez pour le nombre de test de Kmeans un entier(>=1 et <=9) .<br />";
//
$nbreClasseMclustAuto = $_POST["nbreClasseMclustAuto"];
//
if(!empty($msgErreurForm))
{
    exec("$cmdSupp $projetDir");

    $titreErreur = "Erreur dans le saisie du formulaire";
    $imageErreurChemain = "images/symbol-error.png";
    echo msgCadreErreur($titreErreur,$msgErreurForm,$imageErreurChemain);
    exit;
}

$methodeTabNbreTest = array("clara"=>1,"pam"=>1,"kmeans"=>$nbreTestKmeans,"hclust"=>1,"mclust"=>1);
//$methodeNomTab = array("CLARA"=>"clara", "PAM"=>"pam", "K-means $nbreTestKmeans"=>"kmeans", "Hclust"=>"hclust");
//$methodeTabNbreTest = array("clara"=>1,"pam"=>1,"kmeans"=>$nbreTestKmeans,"hclust"=>1);
//
//************* fin : donnee par utilisateur *****************//





//************* debut : creation_corpus_purifie.php et creation_mots_corpus_alloue.php ***************//
//
$temps_debut = microtime(true);
$logChaine = "************** creation corpus purifie ****************\n";
//$creationCorpusPurifieMotsAlloueResultatDir = "$resultatsDir/creation_corpus.purifie_mots.alloue";
//
$creationMotsCorpusAlloueResultatDir = "$resultatsDir{$sepOS}corpus_purifie";
$creationCorpusPurifieResultatDir  = "$resultatsDir{$sepOS}corpus_purifie";
//
mkdir($creationMotsCorpusAlloueResultatDir,0777,TRUE);
@mkdir($creationCorpusPurifieResultatDir,0777,TRUE);
//
$nbreOccCorpusFichier =           "$creationMotsCorpusAlloueResultatDir{$sepOS}mots_occurence_corpus.txt";
$motsCorpusAlloueFichier =        "$creationMotsCorpusAlloueResultatDir{$sepOS}mots_corpus_alloue.txt";//on va l'utiliser dans des autres scripts
$corpusPurifieFichier =           "$creationCorpusPurifieResultatDir{$sepOS}corpus_purifee.txt";//on va l'utiliser dans des autres scripts
$corpusPurifieStopListFichier =   "$creationCorpusPurifieResultatDir{$sepOS}corpus_purifee_stoplist.txt";
$motsCorpusNonAlloueFichier =     "$creationCorpusPurifieResultatDir{$sepOS}mot_corpus_non_alloue.txt";
$constantesConfigurationFichier = "$creationCorpusPurifieResultatDir{$sepOS}constantes_configuration.txt";
//
$nbreMotsCorpusAlloue = creationMotsCorpusAlloue($tauxNbreMotsAlloueCorpusMin,$tauxNbreMotsAlloueCorpusMax,
        $corpusFichier,$stopListFichier,$delemiteurPhraseCorpus,$delimiteurFinPhraseCorpus,
        $constantesConfigurationFichier,$nbreOccCorpusFichier,$motsCorpusAlloueFichier,
        $motsCorpusNonAlloueFichier,$corpusPurifieStopListFichier);
//
//
if($nbreMotsCorpusAlloue == 0)
{
    $titreErreur = "Erreur d'utilisateur";

    $msgErreur = "Pour remedier a cette erreur, veuillez diminuer le taux minimal des
        termes a considerer ou d'augmenter le taux maximal des termes a considerer depuis la formulaire.";

    $imageErreurChemain = "images/symbol-error.png";
    echo msgCadreErreur($titreErreur,$msgErreur,$imageErreurChemain);
    exit;
}
//
//creation corpus_purifie
$nbrePhraseCorpusPurifie = creationCorpusPurifie($corpusPurifieStopListFichier,
        $motsCorpusNonAlloueFichier,$motsCorpusAlloueFichier,$constantesConfigurationFichier,
        $corpusPurifieFichier);
//
//
if($nbreMotsCorpusAlloue > $nbrePhraseCorpusPurifie)
{
    $titreErreur = "Erreur d'utilisateur";

    $msgErreur = "Erreur, vous avez entrez un corpus qui a le nombres de phrases plus petit
        que le nombres des termes alloueee.<br>
        nombres de phrases du corpus purifiee : {$nbrePhraseCorpusPurifie}<br>
        nombre de termes allouees : {$nbreMotsCorpusAlloue}<br>
        Pour remedier a ce probleme veuillez diminuer
        le taux minimal des termes a considerer ou mieux ajouter d'autres phrases a votre
        corpus.";

    $imageErreurChemain = "images/symbol-error.png";
    echo msgCadreErreur($titreErreur,$msgErreur,$imageErreurChemain);
    exit;
}
//
$temps_fin = microtime(true);
$logChaine .= "Temps d'execution : ".round($temps_fin - $temps_debut, 2)."\n";
file_put_contents($logGlobalFichier, $logChaine,FILE_APPEND);
//
//************* fin : creation_corpus_purifie.php et creation_mots_corpus_alloue.php ***************//





//************* debut : test_nbre_mot_allouee_nbre_phrase.php ****************//
//
//on test si il y a des termes qui se repetent dans toutes les phrase du corpus purifié
$motInterdits = "";
$trouveMotInterdits = TesterNbreMotAlloueesNbrePhrases($nbreOccCorpusFichier,
        $nbrePhraseCorpusPurifie,$motInterdits);
//
if($trouveMotInterdits == true)
{
    $titreErreur = "Erreur d'utilisateur";

    $msgErreur = "Erreur, il y a des termes qui se repetent dans tout les phrases du corpus purifiee
        et qui sont:<br><br>{$motInterdits}<br>
        Pour remedier a cette erreur, veuillez diminuer le taux minimal des
        termes a considerer depuis la formulaire ou faitte en sorte de supprimer quelques
        occurences de ces termes dans votre corpus .";

    $imageErreurChemain = "images/symbol-error.png";
    echo msgCadreErreur($titreErreur,$msgErreur,$imageErreurChemain);
    exit;
}
//
unset($motInterdits,$trouveMotInterdits);
//
//************* fin : test_nbre_mot_allouee_nbre_phrase.php ****************//



//************* debut : creation_mat_doc_terme.php ***************//
//
$temps_debut = microtime(true);
$logChaine = "************** creation matrice document terme ****************\n";
//
$matDocTermeDir = "$resultatsDir{$sepOS}matrice_document_terme";
mkdir($matDocTermeDir,0777,TRUE);
//
$matDocTermeFichier = "$matDocTermeDir{$sepOS}matrice_document_terme.txt";
//
creationMatDocTerme($corpusPurifieFichier,$motsCorpusAlloueFichier,$matDocTermeFichier);
//
$temps_fin = microtime(true);
$logChaine .= "Temps d'execution : ".round($temps_fin - $temps_debut, 2)."\n";
file_put_contents($logGlobalFichier, $logChaine,FILE_APPEND);
//
//************ fin : creation_mat_doc_terme.php ***************//





//************ debut : acp.r ****************************//
//
$temps_debut = microtime(true);
$logChaine = "************** acp ****************\n";
//
$acpResultatDir = "$resultatsDir{$sepOS}acp";
mkdir($acpResultatDir,0777,TRUE);
//
$valeursPropreFichier = "$acpResultatDir{$sepOS}valeurs_propre.txt";//pour tri_Imm
$matCorrelationFichier = "$acpResultatDir{$sepOS}matrice_correlation.txt";//pour tri_Imm
//
$acpFichier =$rScriptsDir.$sepOS."acp.r";
//
$acpConfR = "rm(list=ls())\n";
$acpConfR .= "matDocTermeFichier = \"$matDocTermeFichier\"\n";
$acpConfR .= "valeursPropreFichier = \"$valeursPropreFichier\"\n";
$acpConfR .= "matCorFichier = \"$matCorrelationFichier\"\n";
//
$acpFlux = fopen($acpFichier, "w");
fwrite($acpFlux,$acpConfR);
fwrite($acpFlux,file_get_contents($acpFichierDisque));
fclose($acpFlux);
//
$cmdAcp = "$cmdR -f \"$acpFichier\"";
exec($cmdAcp);
//
//tester si l'acp a ete effectuer ou non en testant si les deux fichiers matrice de correlation
//et valeurs propres existe.
//if(!file_exists($valeursPropreFichier) || !file_exists($matCorrelationFichier))
//{
//    $titreErreur = "Erreur dans R";
//
//    $msgErreur = "1) Ca pourra que vous avez faire entrez un corpus dont il y a un terme
//    ou plusieurs qui se repete dans tout les document(lignes) du corpus ou vous avez donnee un corpus
//    dont les nombres d'individus(documents) plus grand que nombre de variables(termes).
//    Pour remedier a cette erreur, veuillez diminuer le taux minimal des
//        termes a considerer ou d'augmenter le taux maximal des termes a considerer depuis la formulaire.<br>2) Si l'erreur n'est pas
//    de probleme de 1) alors ressayer de nouveau car peut etre que c'est juste un probleme materiel.";
//
//    $imageErreurChemain = "images/symbol-error.png";
//    echo msgCadreErreur($titreErreur,$msgErreur,$imageErreurChemain);
//    exit;
//
//}
//
$temps_fin = microtime(true);
$logChaine .= "Temps d'execution : ".round($temps_fin - $temps_debut, 2)."\n";
file_put_contents($logGlobalFichier, $logChaine,FILE_APPEND);
//
//************ fin : acp.r *****************************//





if((isset($immContextTab["selecter"]) && $immContextTab["selecter"] == "on")
        || (isset($immTab["selecter"]) && $immTab["selecter"] == "on")) :
//***** debut construire les 2 matrice: $valeursPropre et $matriceCorrelation pour imm et imm_context *****//
//
$valeursPropreTab = fichierVersTableau($valeursPropreFichier);
//
$matriceCorrelation = fichierVersMatrice($matCorrelationFichier,"\t");
//
$matriceCorrelationPonderer = creationMatriceCorrelationPonderer($nbreMotsCorpusAlloue,$matriceCorrelation,$valeursPropreTab);
//
//***** debut construire les 2 matrice: $valeursPropreTab et $matriceCorrelation pour imm et imm_context *****//





//*********** debut : nbre_concepts_expert.php********************//
//
if(empty($nbreConceptsExpert))
    //retourne le nombre de classe expert utilisé seulement dans la cmd R pour faire la classification (extraire des classes)
    $nbreConceptsExpert = nbreConceptsExpert($conceptsExpertFichier);
//
//*********** fin : nbre_concepts_expert.php ********************//





//*********** debut : nbre_cps_kaiser.php ***********************//
//
$ligne = "Nombre de composantes principales pris avec une valeur propre >1\t".
    nbreCpsKaiser($valeursPropreTab)."/{$nbreMotsCorpusAlloue}\n";
file_put_contents($constantesConfigurationFichier, $ligne,FILE_APPEND);



//verifier si le nombre des augmenter est plus petit que les termes alloué ou non. Et si oui alors il y a
//une erreur
if($nbreConceptsExpert >= $nbreMotsCorpusAlloue)
{
    $titreErreur = "Erreur dans le saisie du formulaire";
    $imageErreurChemain = "images/symbol-error.png";
    $msgErreur = "Vous avez donner comme nombres de concepts : {$nbreConceptsExpert};\n
        le nombres des mots alloues est : {$nbreMotsCorpusAlloue};\n
        Veuillez donner un nombre de concepts strictement
        plus grand que le nombre des mots de corpus ou \n Diminuer le
        Taux minimal des termes a considerer et augmenter le taux maximal des termes a considerer.";
    echo msgCadreErreur($titreErreur,$msgErreur,$imageErreurChemain);
    exit;
}




//************ debut : creation_mat_imm.php *********************//
//
//cette etape est obligatoire aussi pour la creation de mat_imm_context
$temps_debut = microtime(true);
$logChaine = "************** creation matrice imm ****************\n";
//
$immResultatsDir = "$resultatsDir{$sepOS}imm_resultats";
mkdir($immResultatsDir,0777,TRUE);
//
$matImmDir = "$immResultatsDir{$sepOS}matrices_imm";
mkdir($matImmDir,0777,TRUE);
//
$matImmFichier = "$matImmDir{$sepOS}imm.txt";//pour tri_Imm
//
creationMatImm($corpusPurifieFichier,$motsCorpusAlloueFichier,$nbrePhraseCorpusPurifie,$matImmFichier);
//
$temps_fin = microtime(true);
$logChaine .= "Temps d'execution : ".round($temps_fin - $temps_debut, 2)."\n";
file_put_contents($logGlobalFichier, $logChaine,FILE_APPEND);
//
//************ fin : creation_mat_imm.php *********************//
endif;//fin test si on va utiliser imm ou imm_context





//************* debut traitement imm *******************//
//
if(isset($immTab["selecter"]) && $immTab["selecter"] == "on")://si la case est coché donc var existe
//


    


//************ debut : tri_Imm.php ***************//
//
$temps_debut = microtime(true);
$logChaine = "************** tri imm ****************\n";
//
$triImmDir = "$matImmDir";
//@mkdir($triImmDir,0777,TRUE);
//
$immCorSommeFichier = "$triImmDir{$sepOS}imm_cor_somme.txt";
$immCorMaxFichier = "$triImmDir{$sepOS}imm_cor_max.txt";
$immInertieSommeFichier = "$triImmDir{$sepOS}imm_cor_ponderer_somme.txt";
$immInertieMaxFichier = "$triImmDir{$sepOS}imm_cor_ponderer_max.txt";
$immMotAxeFichier = "$triImmDir{$sepOS}imm_mot_axe.txt";
//
$matriceImm = fichierVersMatrice($matImmFichier,"\t");

//
triImmSomme($nbreMotsCorpusAlloue,$matriceImm,$matriceCorrelation,$valeursPropreTab,1,$immCorSommeFichier);
triImmMax($nbreMotsCorpusAlloue,$matriceImm,$matriceCorrelation,$valeursPropreTab,1,$immCorMaxFichier);
triImmSomme($nbreMotsCorpusAlloue,$matriceImm,$matriceCorrelationPonderer,$valeursPropreTab,1,$immInertieSommeFichier);//ponderer
triImmMax($nbreMotsCorpusAlloue,$matriceImm,$matriceCorrelationPonderer,$valeursPropreTab,1,$immInertieMaxFichier);//ponderer
$nbreMotAxe = triImmMotAxe($nbreMotsCorpusAlloue,$matriceImm,$matriceCorrelation,$valeursPropreTab,1,$immMotAxeFichier);
//
$temps_fin = microtime(true);
$logChaine .= "Temps d'execution : ".round($temps_fin - $temps_debut, 2)."\n";
file_put_contents($logGlobalFichier, $logChaine,FILE_APPEND);
//
//************ fin : tri_Imm.php ***************//





//************ debut : creation_concepts_imm.php *****************************//
//
$temps_debut = microtime(true);
$logChaine = "************** creation concepts imm(classification) ****************\n";
//
$immConceptsRResultatDir = "$tmpDir{$sepOS}concepts_r_imm";
$immConceptsResultatDir = "$immResultatsDir{$sepOS}concepts_imm";
mkdir($immConceptsResultatDir,0777,TRUE);
$immConceptsEfficacitesDir = "$immResultatsDir{$sepOS}efficacites_concepts_imm";//tjrs fixe
mkdir($immConceptsEfficacitesDir,0777,TRUE);
//
$typeMethodeTabNbreMotMax = array("sans_tri"=>$nbreMotsCorpusAlloue,"cor_somme"=>$nbreMotsCorpusAlloue,"cor_max"=>$nbreMotsCorpusAlloue,
    "cor_ponderer_somme"=>$nbreMotsCorpusAlloue,"cor_ponderer_max"=>$nbreMotsCorpusAlloue,"mot_axe"=>$nbreMotAxe);
$immTrieFichierTab = array("sans_tri"=>$matImmFichier,"cor_somme"=>$immCorSommeFichier,"cor_max"=>$immCorMaxFichier,
    "cor_ponderer_somme"=>$immInertieSommeFichier,"cor_ponderer_max"=>$immInertieMaxFichier,"mot_axe"=>$immMotAxeFichier);
/*
//variable a utiliser
$methodeNomSelecterTab = array();
$methodeNbreTestSelecterTab = array();

$typeMethodeNomSelecterTab = array();
$typeMethodeNbreMotsMaxSelecterTab = array();

$typeMethodeNbreMotMinSelecterTab = array();
$typeMethodeMargeSelecterTab = array();
*/
//
foreach($methodeNomTab as $methodeNom)
{
    //if(isset($immTab["selecter"]) && $immTab[$methodeNom]["selecter"] == "on")//si la case est coché donc var existe
    {
        $methodeNomSelecterTab = array($methodeNom);
        $methodeNbreTestSelecterTab = array($methodeTabNbreTest[$methodeNom]);

        $typeMethodeNomSelecterTab = array();//initialisation
        $typeMethodeNbreMotsMaxSelecterTab = array();//initialisation
        $immTrieFichierSelecterTab = array();
        $typeMethodeNbreMotMinSelecterTab = array();//initialisation
        $typeMethodeMargeSelecterTab = array();//initialisation

        $faireClassification = "n";
        foreach ($typeMethodeNomTab as $typeMethodeNom)
        {

            //if($immTab["selecter"] && $immTab[$methodeNom][$typeMethodeNom]["selecter"] == "on")//si la case est coché donc var existe
            {
                $min = (isset($immTab[$methodeNom][$typeMethodeNom]["min"]))?
                $immTab[$methodeNom][$typeMethodeNom]["min"]:"";

                $marge = (isset($immTab[$methodeNom][$typeMethodeNom]["marge"]))?
                     $immTab[$methodeNom][$typeMethodeNom]["marge"]:"";

                if((is_numeric($min) && $min>=0 && $min<=100) && (is_numeric($marge) && $marge>=1 && $marge<=1000))
                {
                    $faireClassification = "o";
                    $typeMethodeNomSelecterTab[] = $typeMethodeNom;
                    $typeMethodeNbreMotsMaxSelecterTab[] = $typeMethodeTabNbreMotMax[$typeMethodeNom];
                    $immTrieFichierSelecterTab[] = $immTrieFichierTab[$typeMethodeNom];

                    $typeMethodeNbreMotMinSelecterTab[] = round($immTab[$methodeNom][$typeMethodeNom]["min"]*
                        $typeMethodeTabNbreMotMax[$typeMethodeNom]/100);
                    $typeMethodeMargeSelecterTab[] = round($immTab[$methodeNom][$typeMethodeNom]["marge"]);
                }
            }
        }
        if($faireClassification == "o")
        {
            $conceptsImmRFichier = $rScriptsDir.$sepOS."concepts_imm.r";

            creationFichierR($immConceptsRResultatDir,$fonctionConceptFichier,$methodeNomSelecterTab,$typeMethodeNomSelecterTab,
                    $immTrieFichierSelecterTab,$methodeNbreTestSelecterTab,
                    $typeMethodeNbreMotsMaxSelecterTab,$typeMethodeNbreMotMinSelecterTab,$typeMethodeMargeSelecterTab,$motsCorpusAlloueFichier,
                    $nbreConceptsExpert,$ClassificationFichierDisque,$sepOS,$conceptsImmRFichier,$nbreClasseMclustAuto);

            $cmdConceptImm = "$cmdR -f \"$conceptsImmRFichier\"";
            exec($cmdConceptImm);
            classificationIMM($sepOS,$nbreMotsCorpusAlloue,$nbreConceptsExpert,$conceptsExpertFichier,$immConceptsRResultatDir,$immConceptsResultatDir,
                    $immConceptsEfficacitesDir,$typeMethodeNbreMotMinSelecterTab,$typeMethodeMargeSelecterTab,$methodeNomSelecterTab,
                    $methodeNbreTestSelecterTab,$typeMethodeNomSelecterTab,$typeMethodeNbreMotsMaxSelecterTab);
        }
    }
}
//
$temps_fin = microtime(true);
$logChaine .= "Temps d'execution : ".round($temps_fin - $temps_debut, 2)."\n";
file_put_contents($logGlobalFichier, $logChaine,FILE_APPEND);
//
//************ fin : creation_concepts_imm.php *****************************//




//
endif;
//************* fin traitement imm *******************//





//************* debut traitement imm_context *******************//
//
if(isset($immContextTab["selecter"]) && $immContextTab["selecter"] == "on") ://si la case est coché donc var existe
//



    

//******************* creation imm_context ****************//
//
$temps_debut = microtime(true);
$logChaine = "************** creation matrice imm_context ****************\n";
//
$immContextResultatsDir = "$resultatsDir{$sepOS}imm_context_resultats";
mkdir($immContextResultatsDir,0777,TRUE);
//
$matImmContextDir  = "$immContextResultatsDir{$sepOS}matrices_imm_context";
mkdir($matImmContextDir,0777,TRUE);
//
$matImmContextFichier="$matImmContextDir{$sepOS}imm_context.txt";//$matImmContextFichier doit tenir ce nom
//
creationImmContext($corpusPurifieStopListFichier,$motsCorpusAlloueFichier,$matImmFichier ,$matImmContextFichier);
//
$temps_fin = microtime(true);
$logChaine .= "Temps d'execution : ".round($temps_fin - $temps_debut, 2)."\n";
file_put_contents($logGlobalFichier, $logChaine,FILE_APPEND);
//
//****************** fin creation imm_context ****************//





//****************** tri imm_context ********************//
//
$temps_debut = microtime(true);
$logChaine = "************** tri imm_context ****************\n";
//
$triImmDir  = "$matImmContextDir";
@mkdir($triImmDir,0777,TRUE);
$immCorSommeFichier = "$triImmDir{$sepOS}imm_cor_somme.txt";
$immCorMaxFichier = "$triImmDir{$sepOS}imm_cor_max.txt";
$immInertieSommeFichier = "$triImmDir{$sepOS}imm_cor_ponderer_somme.txt";
$immInertieMaxFichier = "$triImmDir{$sepOS}imm_cor_ponderer_max.txt";
$immMotAxeFichier = "$triImmDir{$sepOS}imm_mot_axe.txt";
//
$matriceImm = fichierVersMatrice($matImmContextFichier,"\t");
//
triImmSomme($nbreMotsCorpusAlloue,$matriceImm,$matriceCorrelation,$valeursPropreTab,3,$immCorSommeFichier);
triImmMax($nbreMotsCorpusAlloue,$matriceImm,$matriceCorrelation,$valeursPropreTab,3,$immCorMaxFichier);
triImmSomme($nbreMotsCorpusAlloue,$matriceImm,$matriceCorrelationPonderer,$valeursPropreTab,3,$immInertieSommeFichier);//ponderer
triImmMax($nbreMotsCorpusAlloue,$matriceImm,$matriceCorrelationPonderer,$valeursPropreTab,3,$immInertieMaxFichier);//ponderer
$nbreMotAxe = triImmMotAxe($nbreMotsCorpusAlloue,$matriceImm,$matriceCorrelation,$valeursPropreTab,3,$immMotAxeFichier);
//
$temps_fin = microtime(true);
$logChaine .= "Temps d'execution : ".round($temps_fin - $temps_debut, 2)."\n";
file_put_contents($logGlobalFichier, $logChaine,FILE_APPEND);
//
//**************** tri imm_context ********************//





//************ debut : creation_concepts_imm_context.php *****************************//
//
$temps_debut = microtime(true);
$logChaine = "************** creation concepts imm_context(classification) ****************\n";
//
$immConceptsRResultatDir = "$tmpDir{$sepOS}concepts_r_imm_context";
//
$immConceptsResultatDir = "$immContextResultatsDir{$sepOS}concepts_imm_context";
mkdir($immConceptsResultatDir,0777,TRUE);
//
$immConceptsEfficacitesDir = "$immContextResultatsDir{$sepOS}efficacites_concepts_imm_context";
mkdir($immConceptsEfficacitesDir,0777,TRUE);
//
$typeMethodeTabNbreMotMax = array("sans_tri"=>$nbreMotsCorpusAlloue*3,"cor_somme"=>$nbreMotsCorpusAlloue*3,"cor_max"=>$nbreMotsCorpusAlloue*3,
    "cor_ponderer_somme"=>$nbreMotsCorpusAlloue*3,"cor_ponderer_max"=>$nbreMotsCorpusAlloue*3,"mot_axe"=>$nbreMotAxe*3);
$immTrieFichierTab = array("sans_tri"=>$matImmContextFichier,"cor_somme"=>$immCorSommeFichier,"cor_max"=>$immCorMaxFichier,
    "cor_ponderer_somme"=>$immInertieSommeFichier,"cor_ponderer_max"=>$immInertieMaxFichier,"mot_axe"=>$immMotAxeFichier);
/*
//variable a utiliser
$methodeNomSelecterTab = array();
$methodeNbreTestSelecterTab = array();

$typeMethodeNomSelecterTab = array();
$typeMethodeNbreMotsMaxSelecterTab = array();

$typeMethodeNbreMotMinSelecterTab = array();
$typeMethodeMargeSelecterTab = array();
*/
//
foreach($methodeNomTab as $methodeNom)
{
    //if(isset($immContextTab["selecter"]) && $immContextTab[$methodeNom]["selecter"] == "on")//si la case est coché donc var existe
    {
        $methodeNomSelecterTab = array($methodeNom);
        $methodeNbreTestSelecterTab = array($methodeTabNbreTest[$methodeNom]);

        $typeMethodeNomSelecterTab = array();//initialisation
        $typeMethodeNbreMotsMaxSelecterTab = array();//initialisation
        $immTrieFichierSelecterTab = array();
        $typeMethodeNbreMotMinSelecterTab = array();//initialisation
        $typeMethodeMargeSelecterTab = array();//initialisation

        $faireClassification = "n";
        foreach ($typeMethodeNomTab as $typeMethodeNom)
        {
            //if(isset($immContextTab["selecter"]) && $immContextTab[$methodeNom][$typeMethodeNom]["selecter"] == "on")//si la case est coché donc var existe
            {
                $min = (isset($immContextTab[$methodeNom][$typeMethodeNom]["min"]))?
                     $immContextTab[$methodeNom][$typeMethodeNom]["min"]:"";
                
                $marge = (isset($immContextTab[$methodeNom][$typeMethodeNom]["marge"]))?
                     (int)$immContextTab[$methodeNom][$typeMethodeNom]["marge"]:"";

                if((is_numeric($min) && $min>=0 && $min<=100) && (is_numeric($marge) && $marge>=1))
                {
                    $faireClassification = "o";
                    $typeMethodeNomSelecterTab[] = $typeMethodeNom;
                    $typeMethodeNbreMotsMaxSelecterTab[] = $typeMethodeTabNbreMotMax[$typeMethodeNom];
                    $immTrieFichierSelecterTab[] = $immTrieFichierTab[$typeMethodeNom];

                    $typeMethodeNbreMotMinSelecterTab[] = round($immContextTab[$methodeNom][$typeMethodeNom]["min"]*
                        $typeMethodeTabNbreMotMax[$typeMethodeNom]/100);
                    $typeMethodeMargeSelecterTab[] = round($immContextTab[$methodeNom][$typeMethodeNom]["marge"]);
                }
            }
        }
        if($faireClassification == "o")
        {
            $conceptsImmRFichier = $rScriptsDir.$sepOS."concepts_imm_context.r";

            creationFichierR($immConceptsRResultatDir,$fonctionConceptFichier,$methodeNomSelecterTab,$typeMethodeNomSelecterTab,
                    $immTrieFichierSelecterTab,$methodeNbreTestSelecterTab,
                    $typeMethodeNbreMotsMaxSelecterTab,$typeMethodeNbreMotMinSelecterTab,$typeMethodeMargeSelecterTab,$motsCorpusAlloueFichier,
                    $nbreConceptsExpert,$ClassificationFichierDisque,$sepOS,$conceptsImmRFichier);

            $cmdConceptImm = "$cmdR -f \"$conceptsImmRFichier\"";
            exec($cmdConceptImm);
            classificationIMM($sepOS,$nbreMotsCorpusAlloue,$nbreConceptsExpert,$conceptsExpertFichier,$immConceptsRResultatDir,$immConceptsResultatDir,
                    $immConceptsEfficacitesDir,$typeMethodeNbreMotMinSelecterTab,$typeMethodeMargeSelecterTab,$methodeNomSelecterTab,
                    $methodeNbreTestSelecterTab,$typeMethodeNomSelecterTab,$typeMethodeNbreMotsMaxSelecterTab);
        }
    }
}
//
$temps_fin = microtime(true);
$logChaine .= "Temps d'execution : ".round($temps_fin - $temps_debut, 2)."\n";
file_put_contents($logGlobalFichier, $logChaine,FILE_APPEND);
//
//************ fin : creation_concepts_imm.php *****************************//





//
endif;
//************* fin traitement imm_context *******************//





$logChaine = "\n************** Resume ****************\n";
$tempsGlobalfin = microtime(true);
$logChaine .= "Temps d'execution total : ".round($tempsGlobalfin - $tempsGlobaldebut, 2)."\n";
$logChaine .= 'Utilisation de RAM maximum (Mo) : '.round(memory_get_peak_usage()/(1024*1024),2)."\n";
file_put_contents($logGlobalFichier, $logChaine,FILE_APPEND);
//
//************** fin programme ***************//





//************** debut compression resultat ************//
//
$projetDirCompressSansExt = "$mesProjetsDir{$sepOS}{$nomProjet}_".@date("d_M_Y");
$extensionComp = "7z"; 
//
//renommer si $projetDirCompress existe:
while(file_exists("$projetDirCompressSansExt.$extensionComp"))
{
    $projetDirCompressSansExt = renommer($projetDirCompressSansExt);
}
//
$projetDirCompress = "$projetDirCompressSansExt.$extensionComp";
//
//supprimer de $projetDir les dossiers tmpDir et rScriptsDir
$cmdSuppDir = "$cmdSupp \"$tmpDir\"";
exec($cmdSuppDir);
//$cmdSuppDir = "$cmdSupp \"$rScriptsDir\"";
//exec($cmdSuppDir);
//
//compresser le dossier $projetDir
$cmdCompression = "$cmd7Zip a \"$projetDirCompress\" \"$projetDir\"";
exec($cmdCompression);
//
//supprimer tout le dossier $projetDir
$cmdSuppDir = "$cmdSupp \"$projetDir\"";
exec($cmdSuppDir);
//
//envoyer au client $projetDirC
downloadFichier("$projetDirCompress");
//
//************** fin compression resultat ************//





endif;//si formulaire envoyer
?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=ISO-8859-1" />
    <script  type="text/javascript" src="js/fonctions.js"></script>
    <link rel="stylesheet" type="text/css" href="css/mint_green.css" />
    
    <title>Classification</title>
</head>

<body>
<form method="POST" enctype="multipart/form-data" name="classification" action="">
    <table border="0" class="tableCss">
        <tr>
            <td>Nom du projet :</td>
            <td><input type="text" name="nomProjet" /> (Alpha numerique sans caracteres speciaux) </td>
        </tr>
        <tr>
            <td>Fichier du corpus : </td>
            <td><input type="file" name="corpus" />(Document par ligne avec extension .txt obligatoire)</td>
        </tr>
        <tr>
            <td colspan="2">+Champs optionnels du corpus :</td>
        </tr>
        <tr>
            <td>++Delimiteur de fin de document :</td>
            <td><input type="text" name="delimFinPhrase" /> (Par defaut c'est : "\n")</td>
        </tr>
        <tr>
            <td>++Delimiteur entre les termes du document :</td>
            <td><input type="text" name="delimEntrePhrase" /> (Par defaut c'est espace: " ")</td>
        </tr>
        <tr>
            <td>++Taux minimal des termes a considerer % :</td>
            <td><input type="text" name="tauxMin" /> (Par defaut c'est 0)</td>
        </tr>
        <tr>
            <td>++Taux maximal des termes a considerer % :</td>
            <td><input type="text" name="tauxMax" /> (Par defaut c'est 100)</td>
        </tr>
        <tr>
            <td>Fichier des concepts experts : </td>
            <td><input type="file" name="conceptsExpert" /> (Concept par ligne avec extension .txt obligatoire)</td>
        </tr>
        <tr>
            <td>+Nombre des concepts experts :</td>
            <td><input type="text" name="nbreConceptsExperts" />(Donnez un nombre des concepts que vous
                l'estimez dans le cas ou vous n'avez pas un fichier des conepts experts)</td>
        </tr>
        <tr>
            <td>Liste des termes a enlever : </td>
            <td><input type="file" name="stopList" /> (Terme par ligne avec extension .txt obligatoire)</td>
        </tr>
        <tr>
            <td colspan="2">Choix de methode de classification</td>
        </tr>
        <tr>
            <td>+Nombre de test Kmeans: </td>
            <td><input type="text" name="nbreTestKmeans" value="<?php echo $nbreTestKmeans; ?>"/></td>
        </tr>
        <tr>
            <td>+Nombre de classe auto de mclust: </td>
            <td>
                <SELECT name="nbreClasseMclustAuto" size="1">
                    <option>non</option>
                    <option>oui</option>
                </SELECT>
            </td>
        </tr>
    </table>
    <br />
        <hr width="80%" />
    <br />
    <?php
        echo creationInterfaceGraphiqueImm($immTypeTab,$methodeNomTab,$typeMethodeNomTab);
    ?>
        <hr width="80%" />
            <center><input type="submit" name="executer" value="Executer" style="font-size: 40px;width: 900px"/></center>
        <hr width="80%" />
</form>

</body>
</html>
