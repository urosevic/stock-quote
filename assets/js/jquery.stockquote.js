jQuery(document).ready(function() {
	// Update AlphaVantage quotes
	setTimeout(function() {
		jQuery.ajax({
			type: 'post',
			dataType: 'json',
			async: true,
			url: stockQuoteJs.ajax_url,
			data: {
				'action': 'stockquote_update_quotes'
			}
		}).done(function(response){
			console.log( 'Stock Quote update quotes response: ' + response.message );
		});
	}, 2000);
});
