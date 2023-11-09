(function( $ ) {
	'use strict';

	$( function() {
		$( 'body' ).on( 'click', 'label', function() {
			$(this).parent().find('.checked').removeClass('checked');
			$(this).addClass('checked')

			$(this).parent().find('.malgapayments-method-form').hide();
			const key = $(this).find('input').val();
			$('#malgapayments-'+key+'-form').show();
			if( $('#malgapayments-'+key+'-form input').length >  0){
				$('#malgapayments-'+key+'-form input')[0].focus();
			}
		});

		$( 'body' ).on( 'change', '#malgapayments-card-expiry', function() {
			const date = $(this).val();
            if( /[0-9]{2} \/ [0-9]{2}$/.test( date ) ){
				$(this).val( date.substr(0,5) + '20' + date.substr(5,2)  );
			}
        });

	});

}( jQuery ));
