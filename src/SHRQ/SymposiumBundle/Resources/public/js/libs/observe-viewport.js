/**
USAGE:
$('.set-of-elements').observeViewport({});
$('#one-element').on('got-visible', function() {
	// do something when element got visible
}).on('got-hidden', function() {
	// do something when element got hidden
});
*/

$.fn.observeViewport = function(config) {
	
	var $this = $(this),
		l = $this.length,
		viewport,
		w = $(window),
		defaultOptions = {
			type: 'third' // 'partial', 'complete', 'third',
		},
		options = {};

	if (typeof config !== 'obect') {
		config = {}
	}
	$.extend(options, config, defaultOptions);
	
	// get coords
	var getCoords = function() {
		for (var i = 0; i < l; i++) {
			var obj = $this[i],
			    $obj = $(obj);
			obj.penis = {
				width: $obj.width(),
				height: $obj.height(),
				offset: $obj.offset()
			};
			$obj.penis.visible = isVisible(obj.penis, viewport);
		}
	}

	// width - height - not necessary always
	var getViewport = function() {
		viewport = {
			width: w.width(),
			height: w.height(),
			offset: { top: w.scrollTop(), left: w.scrollLeft() }
		}
	}

	var onScroll = function() {
		getViewport();
		visibilityChanged();
	}

	var onResize = function() {
		getViewport();
		getCoords();
		visibilityChanged();
	}

	var visibilityChanged = function() {
		for (var i = 0; i < l; i++) {
			var obj = $this[i];
			var oldVisible = obj.penis.visible;
			obj.penis.visible = isVisible(obj.penis, viewport);

			if (!oldVisible && obj.penis.visible) {
				$(obj).addClass('visible').trigger('got-visible');
			} else if (oldVisible && !obj.penis.visible) {
				$(obj).removeClass('visible').trigger('got-hidden');
			}
		}
	}

	var isVisible = function(coords, viewport) {
		switch (options.type) {
			case 'partial':
				return isVisiblePartial(coords, viewport);
			case 'completely':
				return isVisibleCompletely(coords, viewport);
			default:
				return isVisibleThird(coords, viewport);
		}
	}

	var isVisiblePartial = function(coords, viewport) {
		var l = coords.offset.left, t = coords.offset.top, r = l + coords.width, b = t + coords.height;
		var vl = viewport.offset.left, vr = vl + viewport.width, vt = viewport.offset.top, vb = vt + viewport.height;

		return 	l >= vl && l <= vr && t >= vt && t <= vb //lt corner
				||
				r >= vl && r <= vr && t >= vt && t <= vb // rt corner
				||
				l >= vl && l <= vr && b >= vt && b <= vb // lb corner
				||
				r >= vl && r <= vr && b >= vt && b <= vb // rb corner
		;
	}

	var isVisibleThird = function (coords, viewport) {
		var l = coords.offset.left +  coords.width / 2, t = coords.offset.top + coords.height / 2; // center of element
		var vl = viewport.offset.left + viewport.width / 3, vr = vl + viewport.width / 3, vt = viewport.offset.top + viewport.height / 3, vb = vt + viewport.height / 3; // inner third of viewport

		return 	l >= vl && l <= vr && t >= vt && t <= vb;
	}

	var isVisibleCompletely = function (coords, viewport) {
		var l = coords.offset.left, t = coords.offset.top, r = l + coords.width, b = t + coords.height;
		var vl = viewport.offset.left, vr = vl + viewport.width, vt = viewport.offset.top, vb = vt + viewport.height;

		return 	l >= vl && l <= vr && t >= vt && t <= vb //lt corner
				&&
				r >= vl && r <= vr && t >= vt && t <= vb // rt corner
				&&
				l >= vl && l <= vr && b >= vt && b <= vb // lb corner
				&&
				r >= vl && r <= vr && b >= vt && b <= vb // rb corner
		;
	}

	// init
	getViewport();
	getCoords();
	
	if (typeof w.smartresize !== 'undefined') {
		w.smartresize(onResize);
	} else {
		w.resize(onResize);
	}
	w.scroll(onScroll);
}
