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
    },
    
    //On ferme la lightbox
    fermer: function ()
    {
        this.toggleSelectElements('visible');
        $('PointRelais').remove();
    },
    
    baseUrl: ""
}