jQuery(document).ready(function($) {
	$('button[name="sq_force_data_fetch_stop"]').on('click', function(e){
		e.preventDefault();
		$(this).data('stop','true');
	});
	$('button[name="sq_force_data_fetch"]').on('click', function(e){
		e.preventDefault();
		var fetch_button = this;
		var fetch_button_stop = $('button[name="sq_force_data_fetch_stop"]');
		var av_api_tier = $('select[name="stockquote_defaults[av_api_tier]"]').val();
		var av_api_timeout = ( 60 / av_api_tier ) * 1000;

		// disable button
		$(fetch_button).prop('disabled',true);
		$(fetch_button_stop).addClass('enabled');
		/* First reset fetching loop */
		$.ajax({
			type: 'post',
			dataType: 'json',
			async: true,
			url: stockQuoteJs.ajax_url,
			data: {
				'action': 'stockquote_purge_cache'
			}
		}).done( function(response) {
				// Update log container
				$('.sq_force_data_fetch').html( 'Reset fetching loop and fetch data again. Please wait...<br /><br />' );
				function fetchNextSymbol() {
					/* Then do AJAX request */
					$.ajax({
						type: 'post',
						dataType: 'json',
						async: true,
						url: stockQuoteJs.ajax_url,
						data: {
							'action': 'stockquote_update_quotes'
						}
					}).done(function(response) {
						if ( ! response.done && 'true' != $(fetch_button_stop).data('stop') ) {
							// different progress character for timedout request
							if ( response.message.indexOf('Operation timed out') >= 0 ) {
								$('.sq_force_data_fetch').append( '[Timeout] ' + response.symbol + '<br />');
							} else if ( response.message.indexOf('Invalid API call') >= 0 ) {
								var fetch_url = stockQuoteJs.avurl;
								$('.sq_force_data_fetch').append( '[Invalid API call] ' + response.symbol + ' (<a href="' + stockQuoteJs.avurl + response.symbol + '" target="_blank">test</a>)<br />');
							} else {
								$('.sq_force_data_fetch').append( '[OK] ' + response.symbol + '<br />');
							}
							setTimeout(function() {
								fetchNextSymbol();
							}, av_api_timeout);
						} else {
							if ( response.message != 'DONE' ) {
								$('.sq_force_data_fetch').append( '<br />[' + response.symbol + '] ' + response.message );
							} else {
								$('.sq_force_data_fetch').append( '<br />DONE' );
							}
							// Enable button again when all is finished
							$(fetch_button).prop('disabled',false);
							$(fetch_button_stop).removeClass('enabled').data('stop','false');
						}
					}).fail(function(response) {
						$('.sq_force_data_fetch').append( '<br />[Error] ' + response.message );
					});
				};
				fetchNextSymbol();
		});
	});
});