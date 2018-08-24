<?php

namespace Allex\Module;

use Allex\Container;

class Upgrade extends Abstract_Module {
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
	 * @var \Twig_Environment
	 */
	protected $twig;

	/**
	 * Upgrade constructor.
	 *
	 * @param Container $container
	 */
	public function __construct( Container $container ) {
		parent::__construct( $container );

		$this->plugin_basename = $this->container['PLUGIN_BASENAME'];
		$this->plugin_name     = $this->container['PLUGIN_NAME'];
		$this->twig            = $this->container['twig'];
	}

	/**
	 * Add an Upgrade link to the action links in the plugin list.
	 *
	 * @param string $addons_page_url
	 */
	public function init( $addons_page_url ) {
		$this->url = $addons_page_url;

		$this->add_hooks();
	}

	/**
	 * Add the hooks.
	 */
	protected function add_hooks() {
		add_action( 'plugin_action_links_' . $this->plugin_basename, [ $this, 'plugin_action_links' ],
			999 );
	}

	/**
	 * @param array $links
	 */
	public function plugin_action_links( $links ) {
		$context = [
			'url'   => $this->url,
			'label' => __( 'Upgrade', 'allex' ),
			'class' => $this->plugin_name,
		];

		$link = $this->twig->render( 'action_link_upgrade.twig', $context );

		$links = array_merge( $links, [ $link ] );

		return $links;
	}
}
