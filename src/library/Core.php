<?php

namespace Allex;

use Allex\Module\Assets;
use Allex\Module\Reviews;

class Core {

    /**
     * @var Container
     */
    protected $container;

    /**
     * @var Textdomain
     */
    protected $textdomain;

    /**
     * @var Reviews
     */
    protected $module_reviews;

    /**
     * @var Assets
     */
    protected $module_assets;

    /**
     * Core constructor.
     *
     * @param string $plugin_base_name
     */
    public function __construct( $plugin_base_name ) {
        $this->init_container( $plugin_base_name );

        $this->textdomain     = $this->get_service( 'textdomain' );
        $this->module_reviews = $this->get_service( 'module_reviews' );
        $this->module_assets  = $this->get_service( 'module_assets' );
    }

    /**
     * @param $plugin_base_name
     */
    protected function init_container( $plugin_base_name ) {
        $this->container = new Container( [ 'PLUGIN_BASENAME' => $plugin_base_name ] );

        return $this->container;
    }

    /**
     * @param $service_name
     *
     * @return mixed
     * @throws \Exception
     */
    public function get_service( $service_name ) {
        $container = $this->get_container();

        if ( ! isset( $container[ $service_name ] ) ) {
            throw new \Exception( 'Service ' . $service_name . ' is undefined.' );
        }

        return $container[ $service_name ];
    }

    /**
     * @return mixed
     */
    public function get_container() {
        return $this->container;
    }

    /**
     * Initialize the framework.
     */
    public function init() {
        $this->init_textdomain();
        $this->init_assets();
        $this->add_hooks();

        do_action( 'allex_loaded' );
    }

    /**
     * Load the text domain.
     */
    protected function init_textdomain() {
        $this->textdomain->load();
    }

    /**
     * Enqueue assets.
     */
    protected function init_assets() {
        $this->module_assets->init();
    }

    /**
     * Initialize the hooks.
     */
    protected function add_hooks() {
        // Reviews
        add_action( 'allex_enable_module_reviews', [ $this->module_reviews, 'init' ] );
    }
}
