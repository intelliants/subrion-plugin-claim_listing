$(function()
{
	var $claimModal = $('#js-claim-modal');

	$('select[name="type"]', $claimModal).on('change', function()
	{
		var type = $('option:selected', this).val();

		$('.js-options, #js-cl-option-descriptions > span').hide();
		$('.js-option-' + type + ', span[data-type="' + type + '"]').show();
	});

	$('input', '.js-option-manual').on('change', function()
	{
		var $group = $('.js-option-manual'),
			disableBtn = false;

		$('input[type="text"]', $group).each(function()
		{
			if ('' == $(this).val() && !disableBtn) {disableBtn = true; return false;}
		});

		$('button[type="submit"]', $group).prop('disabled', disableBtn);
	});

	$('.js-cmd-check-url', $claimModal).on('click', function()
	{
		var url = $('.js-check-url', $claimModal).text(),
			$btnCheck = $(this);

		$btnCheck.button('loading');

		$.ajax(
		{
			url: intelli.config.ia_url + 'claim/read.json',
			method: 'post',
			data: {action: 'url', url: url, filename: $('.js-check-filename', $claimModal).val()},
			success: function(response)
			{
				var $box = $('.js-url-check-box', $claimModal),
					$btnClaim = $('button[type="submit"]', '.js-option-ftp');

				if (response.result)
				{
					$box.removeClass('alert-danger').addClass('alert-success');
					$btnClaim.show();
				}
				else
				{
					$box.removeClass('alert-success').addClass('alert-danger');
					$btnClaim.hide();
				}

				$box.text(response.message).slideDown('fast');
				$btnCheck.button('reset');
			}
		});
	});

	$('.js-confirm-checkbox', $claimModal).on('click', function()
	{
		$('button[type="submit"]', '.js-option-email').prop('disabled', !$(this).is(':checked'));
	});

	$('form', $claimModal).on('submit', function(e)
	{
		e.preventDefault();

		var approvalType = $('option:selected', 'select[name="type"]').val();

		if ('manual' == approvalType)
		{
			var fields = ['name', 'email', 'phone', 'job_title'],
				isError = false;

			$.each(fields, function(i, fieldName)
			{
				var $field = $('input[name="' + fieldName + '"]');
				if ('' == $field.val())
				{
					$field.closest('.control-group').addClass('error');
					$field.next().show();
					if (!isError)
					{
						isError = true;
					}
				}
				else
				{
					$field.closest('.control-group').removeClass('error');
					$field.next().hide();
				}
			});

			if (isError)
			{
				return;
			}
		}

		$.ajax(
		{
			url: $(this).attr('action'),
			type: $(this).attr('method'),
			data: $(this).serialize(),
			success: function(response)
			{
				$claimModal.modal('hide');
				intelli.notifBox({msg: response.message, type: response.result ? 'success' : 'error'});

				if ('success' == response.type)
				{
					$('#js-cmd-claim').remove();
				}
			}
		});
	});
});