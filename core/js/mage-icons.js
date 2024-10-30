/**
 * Dashicons Picker
 *
 * Based on: https://github.com/bradvin/dashicons-picker/
 */

( function ( $ ) {

	/**
	 *
	 * @returns {void}
	 */
	$.fn.dashiconsPicker = function () {

		/**
		 * Dashicons, in CSS order
		 *
		 * @type Array
		 */
		return this.each( function () {

			var button = $( this ),offsetTop,offsetLeft;
				
			button.on( 'click.dashiconsPicker', function (e) {
				offsetTop = $( e.currentTarget ).offset().top;
				offsetLeft = $( e.currentTarget ).offset().left;
				createPopup( button );
			} );

			function createPopup( button ) {

				var target = $( button.data( 'target' ) ),
					popup  = $( '<div class="dashicon-picker-container"> \
						<div class="dashicon-picker-control" /> \
						<ul class="dashicon-picker-list" /> \
					</div>' )
						.css( {
							'top':  offsetTop,
							'left': offsetLeft
						} ),
					list = popup.find( '.dashicon-picker-list' );

				for ( var i in icons ) {
					list.append( '<li data-icon="' + icons[i] + '"><a href="#" title="' + icons[i] + '"><img src="'+mageURL +'source/img/'+ icons[i] + '.png" width="16" height="16" /></a></li>' );
				};

				$( 'a', list ).click( function ( e ) {
					e.preventDefault();
					var src = $( this ).find('img').attr( 'src' );
					target.val( src );	
					sample = target.attr( 'id' );			
					$('#'+sample+'_w').val('16');	
					$('#'+sample+'_h').val('16');
					$('#'+sample+'_id').val('');
					$('#mage-'+sample+' img').attr('src',src);  
					removePopup();
				} );
				
				var control = popup.find( '.dashicon-picker-control' );

				control.html( '<a data-direction="back" href="#"> \
					<span class="dashicons dashicons-arrow-left-alt2"></span></a> \
					<input type="text" class="" placeholder="Search" /> \
					<a data-direction="forward" href="#"><span class="dashicons dashicons-arrow-right-alt2"></span></a>'
				);

				$( 'a', control ).click( function ( e ) {
					e.preventDefault();
					if ( $( this ).data( 'direction' ) === 'back' ) {
						$( 'li:gt(' + ( icons.length - 26 ) + ')', list ).prependTo( list );
					} else {
						$( 'li:lt(25)', list ).appendTo( list );
					}
				} );
				popup.appendTo( 'body' ).show();
				
				$( 'input', control ).on( 'keyup', function ( e ) {
					var search = $( this ).val();
					if ( search === '' ) {
						$( 'li:lt(25)', list ).show();
					} else {
						$( 'li', list ).each( function () {
							if ( $( this ).data( 'icon' ).toLowerCase().indexOf( search.toLowerCase() ) !== -1 ) {
								$( this ).show();
							} else {
								$( this ).hide();
							}
						} );
					}
				} );
				popup.appendTo( 'body' ).show();
				$( document ).bind( 'mouseup.dashicons-picker', function ( e ) {
					if ( ! popup.is( e.target ) && popup.has( e.target ).length === 0 ) {
						removePopup();
					}
				} );
			}

			function removePopup() {
				$( '.dashicon-picker-container' ).remove();
				$( document ).unbind( '.dashicons-picker' );
			}
		} );
	};
	$( function () {
		$( '.dashicons-picker' ).dashiconsPicker();
	} );
}( jQuery ) );
