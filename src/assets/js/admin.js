(function (global, $) {
    // Check if we already have the framework.
    if (global.allediaframework) {
        return;
    }

    /**
     * The framework constructor.
     *
     * @constructor
     */
    var Framework = function () {

    };

    /**
     * Add a class to the addons menu allowing to highlight it using CSS.
     *
     * @param href
     */
    Framework.prototype.highlight_submenu = function (href) {
        // Add a custom class to the Extensions menu item for custom styling.
        var menu_found = false,
            interval,
            limit = 20,
            i = 0;

        interval = global.setInterval(
            function () {
                if (menu_found || limit === i) {
                    global.clearInterval(interval);
                    return;
                }

                i++;

                $('#adminmenu ul.wp-submenu li').each(function () {
                    if ($(this).find('a').length > 0) {
                        if ($(this).find('a').attr('href') === href) {
                            // Check if the current menu links to the extensions page.
                            $(this).addClass('alledia-framework-addon-submenu');
                            menu_found = true;
                        }
                    }
                });
            },
            500
        );
    };

    // Add to the global scope.
    global.allediaframework = new Framework();
})(window, jQuery);
