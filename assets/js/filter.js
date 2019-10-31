(function($) {
    $.fn.pugxFilter = function () {
        this.on('hidden.bs.collapse', function () {
            $('button.filter svg').removeClass('fa-angle-right').addClass('fa-angle-down');
        }).on('shown.bs.collapse', function () {
            $('button.filter svg').removeClass('fa-angle-down').addClass('fa-angle-right');
        });
    };
}(jQuery));
