(function($) {
    $.fn.pugxFilter = function (newOptions) {
        let options = {
            callbackHide: null,
            callbackShow: null
        };
        $.extend(options, newOptions);

        this.on('hidden.bs.collapse', function () {
            $('button.filter svg, button.filter i').removeClass('fa-angle-right').addClass('fa-angle-down');
            if (typeof options.callbackHide === 'function') {
                options.callbackHide();
            }
        }).on('shown.bs.collapse', function () {
            $('button.filter svg, button.filter i').removeClass('fa-angle-down').addClass('fa-angle-right');
            if (typeof options.callbackShow === 'function') {
                options.callbackShow();
            }
        });
    };
}(jQuery));
