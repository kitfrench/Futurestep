;(function($) {
	/**
	 * A simple querystring parser.
	 * Example usage: var q = $.parseQuery(); q.fooreturns  "bar" if query contains "?foo=bar"; multiple values are added to an array. 
	 * Values are unescaped by default and plus signs replaced with spaces, or an alternate processing function can be passed in the params object .
	 * http://actingthemaggot.com/jquery
	 *
	 * Copyright (c) 2008 Michael Manning (http://actingthemaggot.com)
	 * Dual licensed under the MIT (MIT-LICENSE.txt)
	 * and GPL (GPL-LICENSE.txt) licenses.
	 **/
	jQuery.parseQuery=function(A,B){var C=(typeof A==="string"?A:window.location.search),E={f:function(F){return unescape(F).replace(/\+/g," ")}},B=(typeof A==="object"&&typeof B==="undefined")?A:B,E=jQuery.extend({},E,B),D={};jQuery.each(C.match(/^\??(.*)$/)[1].split("&"),function(F,G){G=G.split("=");G[1]=E.f(G[1]);D[G[0]]=D[G[0]]?((D[G[0]] instanceof Array)?(D[G[0]].push(G[1]),D[G[0]]):[D[G[0]],G[1]]):G[1]});return D};
})(jQuery);

// cf-location-buttons
;(function($) {
	$(function() {
		$('input.cf-location-button').click(function() {
			window.location = $(this).attr('data-location');
			return false;
		});
	});
})(jQuery);

// cf-deploy admin
;(function($) {
	cfd = {};
	cfd.opts = {
		ajax_url: ajaxurl,
		messages_div: '#cfd-messages',
		send_cancel_button: '#cfd-cancel-batch-send',
		loading: '<div class="cfd-loading">' + cfd_admin_settings.loading + '</div>'
	};
	
	cfd.setLoading = function(target) {
		$(target).html($(cfd.opts.loading));
	};
	
	cfd.fetch = function(func, args, successTrigger, beforeTrigger) {
		opts = {
			url:this.opts.ajax_url,
			type:'POST',
			async:true,
			cache:false,
			dataType:'json',
			data:{
				action:'cfd_ajax',
				cfd_action:func,
				args: args
			},
			beforeSend: function(request) {
				$(cfd).trigger(beforeTrigger || 'ajaxDoBefore', request);
				return; 
			},
			success: function(response) { 
				$(cfd).trigger(successTrigger || 'ajaxSuccess', response);
				return; 
			},
			error: function(xhr, textStatus, e) {
				switch(textStatus) {
					case 'parsererror':
						var _errstring = $('<pre />').text(xhr.responseText);
						var _html = '<p><b>' + cfd_admin_settings.ajax_parse_error + '</b>' +
									' <a href="#" onclick="cfd.toggleAjaxErrorString(this); return false">' + cfd_admin_settings.toggle + '</a></p>' +
									'<pre class="cfd-ajax-error-string" style="display: none;">' + _errstring.html() + '</pre>';
						cfd.doError({
							html:_html,
							message: 'parsererror'
						});
						break;
					default:
						cfd.doError({
							html:'<b>' + cfd_admin_settings.invalid_ajax_response + '</b>',
							message:'invalidajax'
						});
				}
				return; 
			}
		};
		$.ajax(opts);
	};

	cfd.toggleAjaxErrorString = function(obj) {
		$('.cfd-ajax-error-string').slideToggle();
	};
	
	cfd.doError = function(m) {
		$(cfd.opts.messages_div).html(m.html).attr('class', 'error').show();				
		return true;
	};
	
	cfd.clearError = function() {
		$(cfd.opts.messages_div).hide().html().attr('class', '');
	};

// Batch actions
	
	cfd.send_batch = function(batch_id) {		
		cfd.batch_id = batch_id;
		cfd.batch_queue = new ajaxQueue();		

		// queue operations opener
		$.ajax({
			url: cfd.opts.ajax_url,
			type: 'POST',
			data: {
				action: 'cfd_ajax',
				cfd_action: 'open_batch_send',
				args: {
					'batch_id': batch_id
				}
			},
			dataType: 'json',
			success: cfd.batch_status_success,
			async: false // make the routine wait for the batch_session_id
		});

		if (cfd.batch_open_error) {
			return false;
		}

		// queue items
		$('#send-batch-items tbody tr').each(function(i) {
			var _this = $(this);
			cfd.batch_queue.add(
				cfd.opts.ajax_url,
				'POST',
				{
					data: {
						action: 'cfd_ajax',
						cfd_action: 'send_batch_item',
						args: {
							'batch_id': batch_id,
							'batch_item_id': _this.attr('id'),
							'batch_item_object_type': _this.attr('data-object-type'),
							'batch_item_guid': _this.attr('data-guid'),
							'batch_session_token': cfd.batch_session_token,
							'batch_import_id': cfd.batch_import_id
						}
					},
					dataType: 'json',
					success: cfd.batch_item_success
				}
			);
		});

		// queue operations closer
		cfd.batch_queue.add(
			cfd.opts.ajax_url,
			'post',
			{
				data: {
					action: 'cfd_ajax',
					cfd_action: 'close_batch_send',
					args: {
						'batch_id': batch_id,
						'batch_import_id': cfd.batch_import_id,
						'batch_session_token': cfd.batch_session_token
					}
				},
				dataType: 'json',
				success: cfd.batch_status_success
			}
		);

		cfd.batch_queue.run();
	};
	
	cfd.batch_item_success = function(response) {
		_item = $('#send-batch-items tbody tr#' + response.type);
				
		if (response.success) {
			cfd.set_status(_item, 'sent');
			cfd.set_status(_item.next('tr'), 'sending');
		}
		else {
			// halt the batch
			cfd.batch_queue.flush();
			$('#cfd-send-batch-message').html(response.message);
			cfd.set_status(_item, 'error');
		}
	};
	
	cfd.set_status = function(item, status) {
		_item = $(item);
		switch (status) {
			case 'sending':
				_item.attr('class', 'sending').find('td:first-child span').html('Sending&hellip;');
				break;
			case 'sent':
				_item.attr('class', 'sent').find('td:first-child span').html('Sent');
				break;
			case 'error':
				_item.attr('class', 'error').find('td:first-child span').html('Error');
				break;
		}
	};
	
	cfd.batch_status_success = function(response) {
		if (response.success && response.type == 'batch-send-close') {
			window.location.href = cfd_redirect_url;
		}
		if (response.success && response.type == 'batch-send-open') {
			cfd.batch_session_token = response.message.batch_session_token;
			cfd.batch_import_id = response.message.batch_import_id;
			$('#send-batch-items tbody tr:first-child').addClass('sending');
		}
		if (!response.success) {
			$('#cfd-send-batch-message').html(response.message);
			$(cfd.opts.send_cancel_button).attr('disabled', 'disabled');
			cfd.batch_open_error = true;
			return false;
		}
		
		return true;		
	};
	
// Cancel batch send

	cfd.cancel_batch_send = function() {
		cfd.batch_queue.flush();
		$(this).attr('disabled', 'disabled');
		
		var cancel_args = {
			'batch_id': cfd.batch_id,
			'batch_import_id': cfd.batch_import_id,
			'batch_session_token': cfd.batch_session_token
		};
		cfd.fetch('cancel_batch_send', cancel_args, 'cfd-cancel-batch-send-response');
	};

	$(cfd).bind('cfd-cancel-batch-send-response', function(evt, response) {
		$('#cfd-send-batch-message').html(response.message);
		$('#send-batch-items tr.sending').attr('class', 'cancelled');
		$(cfd.opts.send_cancel_button).attr('disabled', 'disabled');
		return true;
	});
		
// Rollback
	
	cfd.rollback_import = function(import_id) {
		if (confirm(cfd_admin_settings.rollback_confirm)) {
			cfd.fetch('rollback_import', {'import_id':import_id}, 'cfd-rollback-import-response');
		}
	};
	
	$(cfd).bind('cfd-rollback-import-response', function(evt, response) {
		if (response.success) {
			window.location.href = window.location.href + '&rollback_success=1';
		}
		else {
			$('#cfd-messages').html(response.message).show();	
		}
	});
	
// Server Settings Comms Test

	cfd.toggleServerTestButton = function() {
		var _testbutton = $('#cfd_settings_test');
		if($('#cfd_settings_remote_server_0_address').val().trim().length > 0 && $('#cfd_settings_remote_server_0_key').val().trim().length > 0) {
			_testbutton.attr('disabled', false);
		}
		else {
			_testbutton.attr('disabled', 'disabled');
		}
	};
	
	cfd.testServerCommsSettings = function(e) {
		$('#cf_deploy_test_comms_results').attr('class', false).addClass('cfd-loading');
		$('#cf_deploy_test_comms_results').show();
		$('#cf_deploy_test_comms_message').hide();
		args = {
			server: $('#cfd_settings_remote_server_0_address').val(),
			key: $('#cfd_settings_remote_server_0_key').val()
		};
		cfd.fetch('test_comms_settings', args, 'cfd-test-comms-settings-response');
		e.preventDefault();
		e.stopPropagation();
	};
	
	$(cfd).bind('cfd-test-comms-settings-response', function(evt, response) {
		$('#cf_deploy_test_comms_results').removeClass('cfd-loading');
		if (response.success) {
			$('#cf_deploy_test_comms_results').html(response.message);
			$('#cf_deploy_test_comms_results').addClass('comms-ok');
		}
		else {
			$('#cf_deploy_test_comms_results').html('Error');
			$('#cf_deploy_test_comms_results').addClass('comms-fail');
			$('#cf_deploy_test_comms_message').html(response.message).show();
		}
	});
	
// Ajax Event Handlers

	$(cfd).bind('cfd-new-key-response', function(evt, response) {
		if (!response.success) {
			cfd.doError({
				html:'<b>' + cfd_admin_settings.new_key_fail + '</b>',
				message:'unknownerror'
			});	
		}
		else {
			$('#cfd_settings_auth_key').val(response.message);
			$('#cfd-new-auth-key').hide();
		}
	});
	
	cfd.init = function() {	
		// auth key field actions
		$('#cfd-new-auth-key').live('click', function() {
			cfd.fetch('generate_key', {}, 'cfd-new-key-response');
		});
		$('#cfd_settings_auth_key').keyup(function() {
			if ($(this).val().length == 0) {
				$('#cfd-new-auth-key').show();
			}
			else {
				$('#cfd-new-auth-key').hide();
			}
		});
		
		// this will need to be more dynamic if we add multiple server support
		$('#cfd_settings_remote_server_0_address, #cfd_settings_remote_server_0_key').keyup(function() {
			cfd.toggleServerTestButton();
		});
		if ($('#cfd_settings_test').size()) {
			cfd.toggleServerTestButton();
			$('#cfd_settings_test').live('click', cfd.testServerCommsSettings);
		}
		
		// item-selects
		$('.item-select').each(function() {
			var _cb = $(this);
			_cb.change(function() {
				var _this = $(this);
				if (_this.is(':checked')) {
					_this.closest('tr').addClass('item-selected');
				}
				else {
					_this.closest('tr').removeClass('item-selected');
				}
			});
			if (_cb.is(':checked')) {
				_cb.closest('tr').addClass('item-selected');
			}
		});
		
		$(cfd).trigger('cfd-init');
	};
	
	cfd.toggle_preflight_submit = function() {
		if ($('td input[type="checkbox"].item-select:checked').size() > 0) {
			$('#batch-preflight-button').attr('disabled', false);
		}
		else {
			$('#batch-preflight-button').attr('disabled', 'disabled');
		}
	}

// Init
	
	$(function() {
		cfd.init();
		
		// batch killswitch - super basic, just clears the queue. Non-resumable action.
		$(cfd.opts.send_cancel_button).live('click', function() {
			cfd.cancel_batch_send();
			return false;
		});
		
		// batch duplicate button
		$('#cfd-duplicate-batch').live('click', function() {
			// simply redirect to the supplied url
			window.location.href = $(this).attr('data-duplicate-url');
		});
		
		// rollback button
		$('input[name="import_rollback"]').live('click', function() {
			_this = $(this);
			import_id = _this.attr('data-import-id');
			cfd.rollback_import(import_id);
			return false;
		});
		
		// date refresh handler
		$('#batch-refresh-button').click(function() {
			var d = $('#batch-start-date').val();
			if (d.length != 0) {
				var q = $.parseQuery();
				q.start_date = d;								
				window.location.search = '?' + $.param(q);
			}
			return false;
		});
		
		$('#batch-delete-button').click(function() {
			if (confirm(cfd_admin_settings.batch_delete_confirm)) {
				return true;
			}
			else {
				return false;
			}
		});
		
		// select-all checkboxes
		$('th input[type="checkbox"].select-all').change(function() {
			_this = $(this);
			if (_this.is(':checked')) {
				_this.closest('table').find('td.has-cb input[type="checkbox"]').attr('checked', 'checked');
			}
			else {
				// @TODO - test me!
				_this.closest('table').find('td.has-cb input[type="checkbox"]').attr('checked', false);				
			}
			cfd.toggle_preflight_submit();
		});
		
		$('td input[type="checkbox"].item-select').change(function() {
			cfd.toggle_preflight_submit();
		});
		if ($('td input[type="checkbox"].item-select').size() > 0) {
			cfd.toggle_preflight_submit();
		}
		
		// rudimentary tab-nav click handlers
		$('#cf-nav li a').live('click', function() {
			var _this = $(this);
			_this.addClass('current')
				.closest('li')
				.siblings()
				.find('a')
				.removeClass('current');
			$(_this.attr('href')).show().siblings().hide();						
			return false;
		});
		
		// trigger first click
		if (window.location.hash.length > 0) {
			$('#cf-nav li a[href="' + window.location.hash + '"]').trigger('click');
		}
		else {
			$('#cf-nav li:first-child a').trigger('click');
		}
		
		// make sure forms submit back to the same tab
		$("form").submit(function(){
			_a = $('#cf-nav li a.current');
			if (_a.length) {
				this.action += _a.attr('href');
			}
			return true;
		});
		
		$('a._toggle').click(function() {
			_this = $(this);
			_tgt = $(_this.attr('href'));
			if (_tgt.is(':hidden')) {
				_tgt.show();
				_this.find('._toggle_action').html('Hide');
			}
			else {
				_tgt.hide();
				_this.find('._toggle_action').html('Show');
			}
			return false;
		});
	});
})(jQuery);