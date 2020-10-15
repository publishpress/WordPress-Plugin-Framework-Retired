<?php

namespace Allex\Module;

use Allex\Container;
use Twig_Environment;

class Upgrade extends Abstract_Module
{
    /**
     * @var string
     */
    protected $url;

    /**
     * @var string
     */
    protected $plugin_basename;

    /**
     * @var string
     */
    protected $plugin_name;

    /**
     * @var string
     */
    protected $plugin_title;

    /**
     * @var Twig_Environment
     */
    protected $twig;

    /**
     * Upgrade constructor.
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        parent::__construct($container);

        $this->plugin_basename = $this->container['PLUGIN_BASENAME'];
        $this->plugin_name     = $this->container['PLUGIN_NAME'];
        $this->plugin_title    = $this->container['PLUGIN_TITLE'];
        $this->twig            = $this->container['twig'];
        $this->assets_base_url = $this->container['ASSETS_BASE_URL'];
    }

    /**
     * Add an Upgrade link to the action links in the plugin list.
     *
     * @param string $addons_page_url
     */
    public function init($addons_page_url)
    {
        $this->url = $addons_page_url;

        $this->add_hooks();
    }

    /**
     * Add the hooks.
     */
    protected function add_hooks()
    {
        add_action(
            'plugin_action_links_' . $this->plugin_basename,
            [$this, 'plugin_action_links'],
            999
        );
    }

    /**
     * @param array $links
     *
     * @return array
     */
    public function plugin_action_links($links)
    {
        $link = '<a href="' . $this->url . '" target="_blank" class="allex-highlight allex-upgrade-link ' . $this->plugin_name . '">'
            . __('Upgrade', 'allex') . '</a>';

        $links = array_merge($links, [$link]);

        return $links;
    }
}
