<?php

namespace Allex\Module;

use Allex\Container;

class Assets extends Abstract_Module {
    /**
     * @var string
     */
    protected $assets_base_url;

    /**
     * @var string
     */
    protected $version;

    /**
     * Assets constructor.
     *
     * @param Container $container
     */
    public function __construct( Container $container ) {
        parent::__construct( $container );

        $this->assets_base_url = $this->container['ASSETS_BASE_URL'];
        $this->version         = $this->container['VERSION'];
    }

    /**
     * Initialize the module loading the hooks.
     */
    public function init() {
        $this->init_hooks();
    }

    /**
     * Initialize the hooks.
     */
    protected function init_hooks() {
        add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_styles' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );
    }

    /**
     * Enqueue styles for the admin UI.
     */
    public function admin_enqueue_styles() {
        wp_enqueue_style(
            'allex',
            $this->assets_base_url . '/css/allex-admin.css',
            [],
            $this->version
        );
    }

    /**
     * Enqueue scripts for the admin UI.
     */
    public function admin_enqueue_scripts() {
        wp_enqueue_script(
            'allex',
            $this->assets_base_url . '/js/allex-admin.js',
            [ 'jquery' ],
            $this->version
        );
    }
}
