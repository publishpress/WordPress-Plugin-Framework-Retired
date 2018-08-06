<?php

namespace AllediaFramework;

class Upgrade {
    protected $url;

    protected $base_name;

    /**
     * Add an Upgrade linkn to the action links in the plugin list.
     */
    public function add_upgrade_action_link( $plugin_base_name, $url ) {
        $this->base_name = $plugin_base_name;
        $this->url       = $url;

        add_action( 'plugin_action_links_' . $this->base_name, [ $this, 'plugin_action_links' ], 999 );
    }

    /**
     * @param array $links
     */
    public function plugin_action_links( $links ) {
        $links = array_merge(
            $links,
            [
                '<a href="' . $this->url . '" target="_blank" id="upstream-upgrade-link">' . __( 'Upgrade',
                    'alledia-framework' ) . '</a>',
            ]
        );

        return $links;
    }
}
