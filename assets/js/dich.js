$(document).ready(function () { 

	$('.dich-fold-header').bind('click', function(){
		$(this).parent().toggleClass('dich-fold-expanded');
	});
	
});

function dichTextIsEmpty( psVal ) 
{ 
	var loNamePattern = new RegExp( "^\\s*$", "i");
	return (loNamePattern.test( psVal )); 
}

function dichTextIsIdentifier( psVal )
{
	var loNamePattern = new RegExp( "^[a-zA-Z]{1}\\w*$", "i");
	return (loNamePattern.test( psVal )); 
}

function dichTextIsURLFriendly( psVal )
{
	var loNamePattern = new RegExp( "^[a-zA-Z\\-_]{1}\\w*$", "i");
	return (loNamePattern.test( psVal )); 
}

if(jQuery) ( function($){
	
	/* UI common extensions */
	$.fn.extend( {
		dichDialog: function( poOptions )
		{				
			return new dichDialog( this, poOptions );
		}
	} );
	
	/* Dich dialog */
	function dichDialog( poElement, poOptions ) 
	{
		this.o = $.extend( {	
			modal: true, 
			draggable: true, 
			width: "400px", 
			resizable: false,
			close: this.onClose ,
			title: "Сообщение",
			buttons: {}
		}, poOptions );
		
		this.$self = $("<div class='dich-dialog'></div>").append(poElement);	
		this.$buttonbar = null;
		
		this.init();		
	}
	dichDialog.prototype = 
	{	
		init: function() 
		{	
			var loSelf = $(this.$self);
		
			for( lsButton in this.o.buttons)
			{
				var loButton = this.o.buttons[ lsButton ];	
				
				if (this.$buttonbar == null)
					this.$buttonbar = $("<div class='dich-dialog-buttonbar'></div>");
					
				this.$buttonbar.append( 
					$('<a class="dich-action-button ' + loButton.className + '" href="#">' + loButton.title + '</a>')
					.click( {action: loButton.action}, function(e){

						if ( e.data.action(this) == true )
							loSelf.dialog("close");
							
					})
				);
			}
			
			if (this.$buttonbar != null)
				this.$self.append( this.$buttonbar );
		
			this.$instance = null;
		},
		show : function()
		{
			this.$instance = 
				this.$self
				.dialog({
					modal: this.o.modal, 
					draggable: this.o.draggable, 
					width: this.o.width, 
					resizable: this.o.resizable,
					close: this.o.close,
					title: this.o.title
				});
		
			return this;
		},
		onClose : function()
		{
			$(this).remove();
		}
	}	
	
})(jQuery);
