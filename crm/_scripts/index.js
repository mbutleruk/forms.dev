/***********************************************************************************************************/

	var delay = 1000;
	var timer;			
	
/***********************************************************************************************************/

	$(document).ready(function(){

		$('.bb-form').iframePostForm(
		{
			post: function()
			{
				$('#ajax_status').html('Saving…').show();
			},
			complete: function(response)
			{
				if (response == 'ERROR')
				{
					$('#ajax_status').html('ERROR').show();
				}
				else
				{
					$('#ajax_status').fadeOut();
				}
			}
		});		
		$('.bb-form input, .bb-form textarea, .bb-form select').click(startTimer).keyup(startTimer);
		$('#txtSignature').click(checkSignature).keyup(checkSignature);
	});
	
/***********************************************************************************************************/

	function startTimer() {

		stopTimer();
		timer = setTimeout("updateForm()", delay);

	}
	
/***********************************************************************************************************/

	function stopTimer() {

		clearTimeout(timer);			

	}
	
/***********************************************************************************************************/
	
	function updateForm() {

		stopTimer();
		$('.bb-form').submit();

	}
	
/***********************************************************************************************************/
	
	function doSignature() {
		$('#confirmsignature').modal('show');
	}

/***********************************************************************************************************/

	function cancelSignature() {

		$('#confirmsignature').modal('hide');

	}

/***********************************************************************************************************/

	function confirmSignature() {

		var signature = $.trim($('#txtSignature').val());
		if(signature!='') {
			$("input[name='form_signature']").val($('#txtSignature').val());
			updateForm();
			$('#confirmsignature').modal('hide');
		}
	}

/***********************************************************************************************************/

	function checkSignature() {

		var signature = $.trim($('#txtSignature').val());
		if(signature=='') {
			$('#confirmsignature .btn-success').addClass('disabled');
		} else {
			$('#confirmsignature .btn-success').removeClass('disabled');
		}

	}

/***********************************************************************************************************/
