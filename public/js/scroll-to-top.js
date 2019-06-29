( function( $ ) {

    $.fn.scrollToTop = function() {

        var scrollButton = $( this );

        /* Hide Button by default */
        scrollButton.hide();

        /* Show Button on scroll down */
        var showButton = function() {

            var window_top = $( window ).scrollTop();

            if ( window_top > 150 ) {
                scrollButton.fadeIn( 200 );
            } else {
                scrollButton.fadeOut( 200 );
            }
        }

        showButton();
        $( window ).scroll( showButton );

        /* Scroll back to top when element is clicked */
        scrollButton.click( function () {
            $( 'html, body' ).animate( { scrollTop: 0 }, 300 );
            return false;
        } );

    };



} ( jQuery ) );


jQuery(document).ready( function($) {

    /* Add Button to HTML DOM */
    $( 'body' ).append( '<button id=\"scroll-to-top\" class=\"scroll-to-top-button '+invscrolltotopoptions.alignment+'\" style="background: '+invscrolltotopoptions.background_color+'; color: '+invscrolltotopoptions.icon_color+';'+invscrolltotopoptions.alignment+ '"></button>' );

    /* Add Scroll To Top Functionality */
    $( '#scroll-to-top' ).scrollToTop();
} );