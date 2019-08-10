function enableField(typeImm)
{
    var type=document.getElementById(typeImm).style.display;

    if(type=='none')
            document.getElementById(typeImm).style.display='block';
    else
            document.getElementById(typeImm).style.display='none';
}

function remplir(idDiv,champsMinNom,champsMargeNom)
{
    var min   = document.forms[0].elements[champsMinNom].value;
    var marge = document.forms[0].elements[champsMargeNom].value;

    var maDiv = document.getElementById(idDiv).getElementsByTagName('input');
    
    for (var champ=0; champ < maDiv.length; champ++)
    {
        champsNom = maDiv[champ].getAttribute('name');
        
        if(champsNom.lastIndexOf("][min]") != -1)
            document.forms[0].elements[champsNom].value = min;

        if(champsNom.lastIndexOf("][marge]") != -1)
            document.forms[0].elements[champsNom].value = marge;
    }
}

