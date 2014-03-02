$(function () {

	var menu = $('#menu').detach(),
		map, markers = [];

	$('body').on('click', 'nav li > a', function(e) {
		if(!$(this).hasClass('link')) {
			$(this).parent('li').siblings().find('.opened').removeClass('opened');
			$(this).addClass('opened').siblings('ul, div, form').addClass('opened');
			google.maps.event.trigger(map, 'resize');
			map.setCenter(new google.maps.LatLng(50.086094, 14.421270));
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
			google.maps.event.trigger(map, 'resize');

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
		google.maps.event.trigger(map, 'resize');

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

	('table td').each(function() {
    	if($(this).find('a').length && !$(this).find('a').hasClass('no-detail')) {
    		$(this).addClass('has-detail');
    	}
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

	var handleProfile = function(e) {
		e.preventDefault();

		var $form = $(this);

		var values = {};
		$.each( $form.serializeArray(), function(i, field) {
			values[field.name] = field.value;
		});
		values['fos_user_profile_form[username]'] = $('#fos_user_profile_form_email').val();

		$.ajax({
			type        : $form.attr('method'),
			url         : $form.attr('action'),
			data        : values,
			success     : function(data) {
				var parent = $form.parent();
				parent.html(data);
				parent.find('.fos_user_profile_edit').on('submit', handleProfile);
				parent.find('.ajax-link').on('click', handleAjaxLinks);
			}
		});

	}

	menu.find('.fos_user_profile_edit').on('submit', handleProfile);

	var handleAjaxLinks = function(e) {
		e.preventDefault();
		var that = $(this);

		$.ajax({
			url: $(this).attr('href'),
			type: 'GET',
			success: function (data) {
				$(that.data('target')).html(data);
				$(that.data('target')).find('.ajax-link').on('click', handleAjaxLinks);
				$(that.data('target')).find('.fos_user_profile_edit').on('submit', handleProfile);
			}
		})
	};

	$('.ajax-link').on('click', handleAjaxLinks);

	function initialize() {
		var mapOptions = {
			center: new google.maps.LatLng(50.086094, 14.421270),
			zoom: 15
		};
		map = new google.maps.Map(menu.find('#map').get(0), mapOptions);

		markers.push(new google.maps.Marker({
			position: new google.maps.LatLng(50.0884285, 14.4225103),
			map: map,
			title:"Maitrea - Dům osobního rozvoje"
		}));
		markers.push(new google.maps.Marker({
			position: new google.maps.LatLng(50.0873831, 14.4173879),
			map: map,
			title:"Municipal Library of Prague"
		}));
		markers.push(new google.maps.Marker({
			position: new google.maps.LatLng(50.0840201, 14.4279772),
			map: map,
			title:"fusion hotel prague"
		}));
		markers.push(new google.maps.Marker({
			position: new google.maps.LatLng(50.08409210000001, 14.4145105),
			map: map,
			title:"Lehka Hlava"
		}));
		markers.push(new google.maps.Marker({
			position: new google.maps.LatLng(50.0883149, 14.4162797),
			map: map,
			title:"Mistral"
		}));
		markers.push(new google.maps.Marker({
			position: new google.maps.LatLng(50.086915, 14.4254801),
			map: map,
			title:"Grand Café Orient"
		}));
		markers.push(new google.maps.Marker({
			position: new google.maps.LatLng(50.086094, 14.421270),
			map: map,
			title:"Čili Bar"
		}));
		markers.push(new google.maps.Marker({
			position: new google.maps.LatLng(50.0839548, 14.4143128),
			map: map,
			title:"Hemingway Bar"
		}));

		menu.find('p[data-marker]').each(function() {
			var i = parseInt($(this).data('marker'));

			google.maps.event.addListener(markers[i], 'mouseover', function() {
				this.setAnimation(google.maps.Animation.BOUNCE);
				$('p[data-marker=' + i + ']').css('background-color', '#ffdbe5');
			});
			google.maps.event.addListener(markers[i], 'mouseout', function() {
				this.setAnimation(null);
				$('p[data-marker=' + i + ']').css('background-color', '');
			});

			$(this).mouseenter(function() {
				markers[i].setAnimation(google.maps.Animation.BOUNCE);
				$('p[data-marker=' + i + ']').css('background-color', '#ffdbe5');
			}).mouseleave(function() {
				markers[i].setAnimation(null);
				$('p[data-marker=' + i + ']').css('background-color', '');
			});
		});
	}
	google.maps.event.addDomListener(window, 'load', initialize);
});