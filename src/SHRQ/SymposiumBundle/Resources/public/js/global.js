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

		}

		e.preventDefault();

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


});