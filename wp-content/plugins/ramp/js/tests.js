(function($) {
	
	$(cfd).bind('cfd-test-comms-response', function(evt, response) {
		$('#cf_deploy_test_comms_results').html(response.message);
		$(cfd.opts.messages_div).hide();
	});
	
	$(cfd).bind('cfd-init', function() {
		// test type selections
		$("#cf_deploy_test_comms .test-actions :radio").each(function() {
			$(this).click(function() {
				_this = $(this);
				_this.siblings(":input.inp-companion").removeAttr("disabled");
				_this.closest("div.cf-elm-block").siblings("div.cf-elm-block").each(function(){
					$(this).find(":input.inp-companion").attr("disabled","disabled");
				});
			});
			if ($(this).is(':checked')) {
				$(this).trigger('click');
			}
		});
		// form submit hash
		$('#cf_deploy_test_comms').submit(function(){
			cfd.setLoading('#cf_deploy_test_comms_results');
			$('#cf_deploy_test_comms_results').addClass('form-section').show();
			var _this = $(this);
			var args = _this.serialize();
			cfd.fetch('test_comms', args, 'cfd-test-comms-response');
			return false;
		});
	});
})(jQuery);