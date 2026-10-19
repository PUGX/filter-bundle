(function($) {
    // forms with QUERY method are rendered as POST with a "_method" field, since browsers
    // can't submit them natively: send a real QUERY request instead.
    // Without JavaScript, the POST request works as fallback.
    const submitQuery = function (event) {
        const form = event.target;
        const override = form.querySelector('input[name="_method"]');
        if (!override || 'QUERY' !== override.value.toUpperCase() || typeof fetch !== 'function') {
            return;
        }
        event.preventDefault();
        fetch(form.action || window.location.href, {
            method: 'QUERY',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams(new FormData(form, event.submitter)),
            credentials: 'same-origin',
            // don't follow the redirect: it would repeat the QUERY request
            redirect: 'manual'
        }).then(function (response) {
            if ('opaqueredirect' === response.type || response.redirected) {
                window.location.reload();

                return;
            }
            // filter not saved (e.g. validation errors): show the rendered page
            return response.text().then(function (html) {
                document.open();
                document.write(html);
                document.close();
            });
        });
    };

    $.fn.pugxFilter = function (newOptions) {
        let options = {
            callbackHide: null,
            callbackShow: null
        };
        $.extend(options, newOptions);

        this.find('form').on('submit', submitQuery);

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
