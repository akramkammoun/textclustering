#les configurations viennet de php (au dessus)

matDocTerme <- read.table(file=matDocTermeFichier,header=TRUE,sep="\t",quote="\"")

nbreMots = ncol(matDocTerme)

#centrage et réduction des données --> cor = T
#calcul des coordonnées factorielles --> scores = T
acp.matDocTerme <- princomp(matDocTerme, cor = TRUE, scores = TRUE)

#obtenir les variances associées aux axes c.-à-d. les valeurs propres
val.propres <- acp.matDocTerme$sdev^2
val.propres

write.table(val.propres,file=valeursPropreFichier,sep="\t",eol="\n",dec=".",row.names=FALSE,col.names="valeurs_propres")

# mat_cor
#**** corrélation variables-facteurs ****

mat_cor <- NULL
for (i in 1:nbreMots)
{
  mat_cor <- cbind(mat_cor,acp.matDocTerme$loadings[,i]*acp.matDocTerme$sdev[i])
}
print(mat_cor)

write.table(mat_cor,file=matCorFichier,sep="\t",eol="\n",dec=".")
