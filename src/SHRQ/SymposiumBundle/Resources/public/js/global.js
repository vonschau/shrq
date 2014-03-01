$(function () {

	var menu = $('#menu').detach();

	$('body').on('click', 'nav li > a', function(e) {

			if(!$(this).hasClass('link')) {

				$(this).parent('li').siblings().find('.opened').removeClass('opened');
				$(this).addClass('opened').siblings('ul, div, form').addClass('opened');

			e.preventDefault();

		}

	});

	$('table a').on('click', function(e) {

		var that = $(this);

		if(!$(this).hasClass('no-detail')) {

			$('table').find('.opened-td').removeClass('opened-td');
			$(this).closest('td').addClass('opened-td');

			var row = $(this).closest('tr').nextAll('.opener').first().find('td');

			menu.appendTo(row);

			var target = $($(this).attr('href'));


			$('#menu').find('.opened').removeClass('opened');

			target
				.addClass('opened')
				.siblings('a').addClass('opened')
				.closest('.person-detail').addClass('opened')
				.siblings('a').addClass('opened')
				.closest('.sub-1').addClass('opened')
				.siblings('a').addClass('opened');

			$('html, body').animate({scrollTop: that.offset().top}, 0);

			e.preventDefault();
		}
	});

   	$('.menu-link').on('click', function() {
		$('table').find('.opened-td').removeClass('opened-td');

		var row = $('table tr.opener:first').find('td');

		menu.appendTo(row);

		var target = $($(this).attr('href'));

		$('#menu').find('.opened').removeClass('opened');

		target
			.addClass('opened')
			.siblings('form').addClass('opened')
			.siblings('a').addClass('opened')
			.closest('.person-detail').addClass('opened')
			.siblings('a').addClass('opened')
			.closest('.sub-1').addClass('opened')
			.siblings('a').addClass('opened');

		$('html, body').animate({scrollTop: menu.offset().top}, 500);
	});


   	var handleRegister = function(e) {
		e.preventDefault();

		var $form = $(this);

		var values = {};
		$.each( $form.serializeArray(), function(i, field) {
			values[field.name] = field.value;
		});
		values['fos_user_registration_form[username]'] = $('#fos_user_registration_form_email').val();

		$.ajax({
			type        : $form.attr('method'),
			url         : $form.attr('action'),
			data        : values,
			success     : function(data) {
				var $data = $(data);
				if ($data.prop('tagName') === 'FORM') {
					$form.replaceWith($data);
					$data.on('submit', handleRegister);
				} else {
					$form.after($data);
					$data.show();
					$data.children('ul').show();
				}
			}
		});

	}

	menu.find('.fos_user_registration_register').on('submit', handleRegister);


});