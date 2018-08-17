(function (global, $) {
    $(function () {
        var loaderString = '<div class="lds-dual-ring"></div>';

        /**
         * The framework constructor.
         *
         * @param plugin_name
         *
         * @constructor
         */
        var Framework = function (plugin_name) {
            this.plugin_name = plugin_name;

            init_tabs(this.plugin_name);
            init_activate_buttons(this.plugin_name);
        };

        /**
         * Add a class to the addons menu allowing to highlight it using CSS.
         *
         * @param string href
         */
        Framework.prototype.highlight_submenu = function (href) {
            // Add a custom class to the Extensions menu item for custom styling.
            var menu_found = false,
                interval,
                limit = 20,
                i = 0,
                self = this;

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
                                $(this).addClass('allex-highlight');
                                $(this).addClass(self.plugin_name);
                                menu_found = true;
                            }
                        }
                    });
                },
                500
            );
        };

        /**
         * Add hooks to the add-ons page.
         *
         * @param string plugin_name
         */
        var init_tabs = function (plugin_name) {
            var wrapper = $('#allex-addons-wrapper.' + plugin_name);

            // Abort if the wrapper is not found.
            if (wrapper.length === 0) {
                return;
            }

            // Main tabs.
            $('a.nav-tab', wrapper).on('click', function (e) {
                e.preventDefault();

                var $self = $(this);

                // Deactive the active container.
                $('.container.active', wrapper).removeClass('active');
                // Deactive the active tab.
                $('.nav-tab-active', wrapper).removeClass('nav-tab-active');

                // Activate the correct tab container.
                $('#allex-addons-' + $self.data('tab')).addClass('active');
                // Activate the active tab.
                $(this).addClass('nav-tab-active');
            });
        };

        /**
         * Initiate the activate license buttons.
         *
         * @param string plugin_name
         */
        var init_activate_buttons = function (plugin_name) {
            $('.allex-license button.activate').on('click', function (e) {
                e.preventDefault();

                var $self = $(this);
                if ($self.attr('disabled') === 'disabled') {
                    return;
                }

                var wrapper = $($self.parents('article[data-addon_name]'));
                var license_key_field = $('input', wrapper);
                var license_key = $('.allex-license-key code', wrapper);
                var button = $(this);
                var loader = $(loaderString);
                var message = $('.allex-message', wrapper);
                var status = $('allex-license-status', wrapper);

                var show_message = function (html) {
                    message.html(html);
                    message.removeClass('allex-hidden');
                };

                var hide_message = function () {
                    message.text('');
                    message.addClass('allex-hidden');
                };

                // Remove error classes.
                license_key_field.removeClass('has-error');

                // If no license key, move focus to the field.
                if (license_key_field.val().length === 0) {
                    license_key_field.focus();
                    show_message(allex_config.labels.empty_license);
                    return;
                }

                $.ajax({
                    type: 'POST',
                    url: ajaxurl,
                    data: {
                        action: 'allex_addon_license_validate',
                        key: license_key_field.val(),
                        plugin_name: $('#allex-plugin-name').val(),
                        addon_name: wrapper.data('addon_name'),
                        nonce: $('#allex-addons-nonce').val()
                    },
                    beforeSend: function (jqXHR, settings) {
                        $self.attr('disabled', 'disabled');
                        license_key_field.attr('disabled', 'disabled');
                        button.text(allex_config.labels.please_wait);
                        button.after(loader);
                        hide_message();
                    },
                    success: function (response, textStatus, jqXHR) {
                        loader.remove();
                        button.text(allex_config.labels.activate);
                        button.attr('disabled', false);
                        license_key_field.attr('disabled', false);

                        if (!response.success) {
                            show_message(response.message);
                        } else {
                            if (response.message) {
                                status.text(response.message);
                                wrapper.addClass('allex-license-' + response.license_status);
                            }

                            if (response.license_status === 'invalid') {
                                license_key_field.focus();
                            } else {

                                // Hide the fields.
                                license_key_field.parent().addClass('allex-hidden');
                                // Update the license key.
                                license_key.text(response.license_key);
                                // Display the license key.
                                license_key.parent().removeClass('allex-hidden');
                            }
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        loader.remove();
                        button.text(allex_config.labels.activate);
                        button.attr('disabled', false);
                        license_key_field.attr('disabled', false);

                        show_message(errorThrown + '<br>' + allex_config.labels.contact_support);

                        license_key_field.attr('disabled', false);
                    }
                });
            });

            $('.allex-license .allex-license-change').on('click', function (e) {
                e.preventDefault();

                var $self = $(this);

                var wrapper = $($self.parents('article[data-addon_name]'));
                var license_key_field = $('input', wrapper);
                var license_key = $('.allex-license-key code', wrapper);
                var button = $(this);
                var message = $('.allex-message', wrapper);
                var status = $('allex-license-status', wrapper);

                // Hide the current license key.
                button.parent().addClass('allex-hidden');
                // Show the fields again.
                license_key_field.parent().removeClass('allex-hidden');
                license_key_field.val(license_key.text());
                status.text('');
                message.text('');
            });
        };

        // Check if we already have the framework.
        if (!global.Allex) {
            global.Allex = Framework;
        }
    });
})(window, jQuery);
