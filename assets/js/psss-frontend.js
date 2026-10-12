/**
 * Pavel Silinskii Social Share — share popups and copy link.
 */
document.addEventListener( 'DOMContentLoaded', function () {
	// Open share links in a popup window.
	document.querySelectorAll( '.psss-open-popup .psss-btn[href]' ).forEach( function ( btn ) {
		btn.addEventListener( 'click', function ( e ) {
			var popup = window.open( btn.href, 'psss_share', 'width=600,height=450,scrollbars=yes,resizable=yes' );

			// If the popup was blocked, let the link open in a new tab instead.
			if ( popup ) {
				e.preventDefault();
			}
		} );
	} );

	// Copy the post URL to the clipboard.
	document.querySelectorAll( '.psss-copy' ).forEach( function ( btn ) {
		btn.addEventListener( 'click', function () {
			try {
				var url = btn.dataset.url;
				var textEl = btn.querySelector( '.psss-text' );
				var original = textEl ? textEl.textContent : '';

				var showCopied = function () {
					if ( ! textEl ) {
						return;
					}
					textEl.textContent = btn.dataset.copied || 'Copied!';
					clearTimeout( btn.psssTimer );
					btn.psssTimer = setTimeout( function () {
						textEl.textContent = original;
					}, 2000 );
				};

				var fallbackCopy = function () {
					var textarea = document.createElement( 'textarea' );
					textarea.value = url;
					textarea.setAttribute( 'readonly', '' );
					textarea.style.position = 'fixed';
					textarea.style.top = '-9999px';
					document.body.appendChild( textarea );
					textarea.select();
					try {
						if ( document.execCommand( 'copy' ) ) {
							showCopied();
						}
					} finally {
						document.body.removeChild( textarea );
					}
				};

				if ( navigator.clipboard && window.isSecureContext ) {
					navigator.clipboard.writeText( url ).then( showCopied ).catch( fallbackCopy );
				} else {
					fallbackCopy();
				}
			} catch ( err ) {
				// Never break the page over a failed copy.
			}
		} );
	} );
} );
