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

		/* Disable Fetch button */
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
				/* Restart log container */
				$('.sq_force_data_fetch').html( 'Reset fetching loop and fetch data again.<br />' );
				$('.sq_force_data_fetch').append( 'Between each symbol fetch we will wait ' + (av_api_timeout/1000) + ' second(s) to fullfill API Key Tier rules. Please wait...<br /><br />' );
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
							if ( $.inArray(response.status, ['wait','skip','timeout','invalid','success']) >= 0 ) {
								var msg = '<strong>' + response.symbol + '</strong> | <em>' + response.message + '</em>';
								if ($.inArray(response.status, ['timeout','invalid']) >= 0) {
									msg += ' (<a href="' + stockQuoteJs.avurl + response.symbol + '" target="_blank">test</a>)';
								}
								msg = '[' + response.status.replace('success','ok').toUpperCase() + '] ' + msg + '<br />';
							} else {
								msg = '[?] <strong>' + response.symbol + '</strong> | <em>' + response.message + '</em><br />';
							}
							$('.sq_force_data_fetch').append(msg);
							setTimeout(function() {
								fetchNextSymbol();
							}, av_api_timeout);
						} else {
							if ( response.message != 'DONE' ) {
								$('.sq_force_data_fetch').append( '<br />[STOP] ' + response.symbol + ': ' + response.message + '<br />Fetch interrupted by user.' );
							} else {
								$('.sq_force_data_fetch').append( '<br />DONE' );
							}
							/* Enable button again when all is finished */
							$(fetch_button).prop('disabled',false);
							$(fetch_button_stop).removeClass('enabled').data('stop','false');
						}
					}).fail(function(response) {
						$('.sq_force_data_fetch').append( '<br />[ERROR] ' + response.message );
					});
				};
				fetchNextSymbol();
		});
	});
});