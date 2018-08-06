<?php

namespace AllediaFramework;

class Core {

    protected $container;

    /**
     * Core constructor.
     *
     * @param string $plugin_base_name
     */
    public function __construct( $plugin_base_name ) {
        $container = $this->set_container( $plugin_base_name );

        $container['text_domain']->load();
        $container['assets']->enqueue_styles();
    }

    /**
     * @return mixed
     */
    public function get_container() {
        return $this->container;
    }

    /**
     * @param $plugin_base_name
     */
    protected function set_container( $plugin_base_name ) {
        $this->container = new Container( [ 'PLUGIN_BASENAME' => $plugin_base_name ] );

        return $this->container;
    }
}
