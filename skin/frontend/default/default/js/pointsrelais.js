var PointsRelais = new Object();
PointsRelais = {
    
    //On nomme le module pour aller chercher le controller
    module: 'pointsrelais',
    
    //On récupère l'url de Base et on ajoute le nom du controller
    getUrl: function()
    {
        return baseUrl+this.module;
    },
    
	//On cache les select à cause d'IE.
	toggleSelectElements : function(visibility)
    {
		var selects = document.getElementsByTagName('select');
		for(var i = 0; i < selects.length; i++) {
			selects[i].style.visibility = visibility;
		};
	},
    
    //On change le pointer de la souris sur les liens
    toggleLinkPointer : function(style)
    {
        var liens = $A($$('a'));
        liens.each( function(element) { element.style.cursor = style; });
    },
    
    //On va chercher les infos du point relais
    showInfo: function(Id)
    {
        document.body.insert({top:'<div id="PointRelais"></div>'});
       
        var hauteur = document.body.getHeight();
        var largeur = document.body.getWidth();
        var protocol = document.location.protocol || 'http:' ;

        if (protocol != 'http:'){
            //ouvrir popup
            if ($('relai_post')){
                $('Id_Relais').value = Id;
                $('Pays').value = $('pays').innerHTML;
                $('hauteur').value = hauteur;
                $('largeur').value = largeur;
            } else {
                var form = new Element('form', { action: this.getUrl(), method: "post", name: "relai_post", id: "relai_post", target: "popup_relais", className: "no-display"});
                var id_relais = new Element('input', { name: "Id_Relais", value:Id, id: "Id_Relais" });
                var pays = new Element('input', { name: "Pays", value: $('pays').innerHTML, id: "Pays" });
                var phauteur = new Element('input', { name: "hauteur", value: hauteur, id: "hauteur" });
                var plargeur = new Element('input', { name: "largeur", value: largeur, id: "largeur" });
                var popup_relais = new Element('input', { name: "popup_relais", value: 1, id: "popup_relais" });
                form.insert(id_relais);
                form.insert(pays);
                form.insert(phauteur);
                form.insert(plargeur);
                form.insert(popup_relais);
                document.body.insert(form);
            }

            window.open(baseUrl+'blank-page', 'popup_relais',' width=900,height=450,scrollbars=no');
            the_form = eval(document.forms['relai_post']);
            the_form.submit();
        } else {
            //ouverture div
            new Ajax.Request( this.getUrl() ,
            {
                evalScripts : true,
                parameters : {Id_Relais: Id, Pays:$('pays').innerHTML, hauteur: hauteur, largeur: largeur},
                onCreate : function() {
                    document.body.style.cursor = 'wait';
                    PointsRelais.toggleLinkPointer('wait');
                },
                onSuccess : function(transport) {
                    document.body.style.cursor = 'default';
                    PointsRelais.toggleLinkPointer('pointer');
                    PointsRelais.toggleSelectElements('hidden');
                    $('PointRelais').update();
                    $('PointRelais').update(transport.responseText);
                }
            });
        }
    },
    
    //On ferme la lightbox
    fermer: function ()
    {
        this.toggleSelectElements('visible');
        $('PointRelais').remove();
    },
    
    baseUrl: ""
}