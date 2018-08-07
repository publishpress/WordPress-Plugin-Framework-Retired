<?php

namespace AllediaFramework;

class Assets {
    /**
     * @var Container
     */
    protected $container;

    /**
     * TextDomain constructor.
     *
     * @param Container $container
     */
    public function __construct( Container $container ) {
        $this->container = $container;
    }

    /**
     * Enqueue styles for the admin UI.
     */
    public function admin_enqueue_styles() {
        wp_enqueue_style(
            'alledia-framework',
            $this->container['ASSETS_BASE_URL'] . '/css/admin.css',
            [],
            $this->container['VERSION']
        );
    }
}
