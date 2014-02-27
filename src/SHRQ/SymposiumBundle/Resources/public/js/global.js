$(function () {

    $('nav li > a').not('.link').on('click', function(e) {

        $(this).parent('li').siblings().find('.opened').removeClass('opened');
        $(this).addClass('opened').siblings('ul, div, form').addClass('opened');

        e.preventDefault();

    });

});