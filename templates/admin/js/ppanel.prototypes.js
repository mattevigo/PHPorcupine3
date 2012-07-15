(function( $ ) {
	
	/**
	 * Widget per la gestione degli uploads di foto da inserire in una galleria fotografica
	 */
	$.widget( "ppanel.photogallery", {

		// These options will be used as defaults
		options: { 
			clear: null,
			headerHidden: false,
			headerTitle: "Photogallery",
			galleryData: null,
			galleryDataUrl: new Array(),
			maxPhotos: 0					// 0 - unlimited photos
		},

		// Set up the widget
		_create: function() {
			console.log("ppanel.photogallery created");
			
			//-- Creazione dell'header
			this.element.append('<div id="foto-header" class="header">'+this.options.headerTitle+'</div>');
			if(this.options.headerHidden) 
			{
				$(this.element, "#foto-header").hide();
			}
			
			//-- Toolbar
			this.element.append('<div id="foto-toolbar"></div>');
			
			//-- Lista UL
			this.element.append('<ul class="photogallery-photo-list"></ul>');
			
			this.element.append('<div class="cleared"></div>');
			$('.header', this.element).addClass('header-standard ui-widget-header ui-corner-all');

			//-- Caricamento data
			if(this.options.galleryUrl != null)
			{
				$.getJSON(this.options.galleryDataUrl, function(data){
					this.options.galleryData = data;
				})
			}
			
			//-- Corpo galleria
			
			// Iterazione nelle foto
		},

		// Use the _setOption method to respond to changes to options
		_setOption: function( key, value ) {

			switch( key )
			{
				case "clear":
					// handle changes to clear option
					console.log("ppanel.photogallery option: clear");
					break;
				
				case "galleryData":
					console.log("ppanel.photogallery adding "+key+": "+value);
					break;
					
				case "galleryDataUrl":
					console.log("ppanel.photogallery adding "+key+": "+value);
					break;
			}
			
			// In jQuery UI 1.8, you have to manually invoke the _setOption method from the base widget
			$.Widget.prototype._setOption.apply( this, arguments );
			
			// In jQuery UI 1.9 and above, you use the _super method instead
			//this._super( "_setOption", key, value );
			$.Widget.prototype._setOption.call( this, key, value );
		},

		// Use the destroy method to clean up any modifications your widget has made to the DOM
		destroy: function() {
			// In jQuery UI 1.8, you must invoke the destroy method from the base widget
			$.Widget.prototype.destroy.call( this );
			// In jQuery UI 1.9 and above, you would define _destroy instead of destroy and not call the base method
		},
		
		refresh: function() {
			
		},
		
		/**
		 * Aggiunge una foto
		 * photo: {url:"url_immagine", thumb:"url_thumbnail", upload_id:"upload_id"}
		 */
		addPhoto: function(photo) {
			
			console.log("ppanel.photogallery addPhoto: "+photo.url);
			var list = $('ul.photogallery-photo-list', this.element);
			
			list.append(	
				'<li id="'+photo.upload_id+'" class="foto">'+
					'<div class=foto>'+
						'<a href="'+photo.url+'" class="foto-url"><img class="foto" src="uploads/timthumb.php?w=100&h=100&src='+photo.url+'" /></a>'+
						'<img class="delete-foto" src="templates/admin/img/button-delete.png" />'+
					'</div>'+
				'</li>'
			);
			
			$('a.foto-url').fancybox();
			
			var li = $('li#'+photo.upload_id, list);
			
			$('img.delete-foto', li).css({
				'margin-top':'2px'
			}).click(function(eventObj){
				// Rimozione foto
				var id = $(this).parent().parent().attr('id');
				console.log("Photogallery: Eliminazione foto "+id);
				
				$('li#'+photo.upload_id).remove();
				$('input.photogallery[value='+id+']').remove();
				
				// Rinomino tutti gli input di categoria
				$('input.photogallery').each(function(index, Element){
					$(this).attr('name', 'foto_'+index);
				});
			});
			
			$('li.foto', list).css({
				'margin-top':'10px'
			}).addClass('bordered');
			
			$('div.foto', list).css({
				'width':'100px',
				'height':'120px',
				'text-align':'right'
			});
			
			this.element.append('<input type="hidden" class="photogallery" name="foto" value="'+photo.upload_id+'" rel="'+""+'" />');
			
			// Rinomino tutti gli input di categoria
			$('input.photogallery').each(function(index, Element){
				$(this).attr('name', 'foto_'+index);
			});
		}
	});
	
}( jQuery ) );