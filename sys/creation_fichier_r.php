<?php
function creationFichierR($immConceptsRResultatDir,$fonctionConceptFichier,$methodeTabName,$typeMethodeTabName,
        $immTrieFichierTab, $methodeTabNbreTest,$typeMethodeTabNbreMotMax,$typeMethodeTabNbreMotMin,$typeMethodeTabMarge,
        $motsCorpusAlloueFichier,$nbreConceptsExpert,$conceptsImmFichierDisque,$sepOS,$conceptsImmRFichier,
        $nbreClasseMclustAuto)
{
	$conceptImmConfR  = "rm(list=ls())\n";
	$conceptImmConfR .= "sepSystem <- \"$sepOS\"\n";
	$conceptImmConfR .= "source(file=\"$fonctionConceptFichier\")\n";
	$conceptImmConfR .= "resultatConceptsDir <- \"$immConceptsRResultatDir\"\n";
	
	$methodeNameLigne = implode("\",\"", $methodeTabName);
	$conceptImmConfR .= "methodeTabName <- c(\"$methodeNameLigne\")\n";
	
	$typeMethodeNameLigne = implode("\",\"", $typeMethodeTabName);
	$conceptImmConfR .= "typeMethodeTabName <- c(\"$typeMethodeNameLigne\")\n";

        $immTrieFichierLigne = implode("\",\"", $immTrieFichierTab);
	$conceptImmConfR .= "typeMethodeTabFichier <- c(\"$immTrieFichierLigne\")\n";
	
	$methodeNbreTestLigne = implode(",", $methodeTabNbreTest);
	$conceptImmConfR .= "methodeTabNbreTest <- c($methodeNbreTestLigne)\n";
	
	$typeMethodeNbreMotMaxLigne = implode(",", $typeMethodeTabNbreMotMax);
	$conceptImmConfR .= "typeMethodeTabNbreMotMax <- c($typeMethodeNbreMotMaxLigne)\n";
	
	$typeMethodeNbreMotMinLigne = implode(",", $typeMethodeTabNbreMotMin);
	$conceptImmConfR .= "typeMethodeTabNbreMotMin <- c($typeMethodeNbreMotMinLigne)\n";
	
	$typeMethodeMargeLigne = implode(",", $typeMethodeTabMarge);
	$conceptImmConfR .= "typeMethodeTabMarge <- c($typeMethodeMargeLigne)\n";
	
	$conceptImmConfR .= "fichierMots <- \"$motsCorpusAlloueFichier\"\n";
	$conceptImmConfR .= "nbreConceptsExpert <- $nbreConceptsExpert\n";
        $conceptImmConfR .= "nbreClasseMclustAuto <- \"$nbreClasseMclustAuto\"\n";

        //concat les configurations aves le fichier r
	$conceptsImmFlux = fopen($conceptsImmRFichier, "w");
	fwrite($conceptsImmFlux,$conceptImmConfR);
	fwrite($conceptsImmFlux,file_get_contents($conceptsImmFichierDisque));
	fclose($conceptsImmFlux);
}
?>
