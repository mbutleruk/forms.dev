/***********************************************************************************************************/

	var delay = 1000;
	var animationSpeed = 500;
	var timer;

/***********************************************************************************************************/

	$(document).ready(function(){

		$('.bb-form').iframePostForm(
		{
			post: function() {
				$('.ajax-status').stop(false, true).html('Saving').css({'right':-100}).animate({'right':5}, animationSpeed);
			},
			complete: function(response) {
				if (response == 'ERROR') {
					$('.ajax-status').html('ERROR');
				}
				else {
					$('.ajax-status').animate({'right':-100}, animationSpeed);
				}
				if (response == 'SIGNED') {
					document.location.reload();
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
		$('#sign-document').show();
	}

/***********************************************************************************************************/

	function cancelSignature() {

		$('#sign-document').hide();

	}

/***********************************************************************************************************/

	function confirmSignature() {

		var signature = $.trim($('#txtSignature').val());
		if(signature!='') {
			$("input[name='form_signature']").val($('#txtSignature').val());
			updateForm();
			$('sign-document').hide();
		}
	}

/***********************************************************************************************************/

	function checkSignature() {

		var signature = $.trim($('#txtSignature').val());
		if(signature=='') {
			$('#sign-confirmation').addClass('disabled');
		} else {
			$('#sign-confirmation').removeClass('disabled');
		}

	}

/***********************************************************************************************************/
