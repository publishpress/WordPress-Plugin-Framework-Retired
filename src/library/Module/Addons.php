<?php

namespace Allex\Module;

use Allex\Container;

class Addons extends Abstract_Module {
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
	protected $plugin_dir_path;

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
		$this->plugin_dir_path = $this->get_plugins_dir_path();
		$this->twig            = $this->container['twig'];
	}

	/**
	 * @return bool|string
	 */
	protected function get_plugins_dir_path() {
		return dirname( dirname( dirname( dirname( dirname( dirname( dirname( __DIR__ ) ) ) ) ) ) );
	}

	/**
	 * Add an Upgrade link to the action links in the plugin list.
	 */
	public function init() {
		$this->add_hooks();
	}

	/**
	 * Add the hooks.
	 */
	protected function add_hooks() {
		add_action( 'alex_echo_addons_page', [ $this, 'echo_addons_page' ], 10, 2 );
	}

	/**
	 *
	 *     $addons = [
	 *          [
	 *              'slug'        => '',
	 *              'title'       => '',
	 *              'description' => '',
	 *              'icon'        => '',
	 *          ],
	 *     ],
	 *
	 * @param string $addons_page_url
	 * @param array  $addons
	 *
	 * @throws \Twig_Error_Loader
	 * @throws \Twig_Error_Runtime
	 * @throws \Twig_Error_Syntax
	 */
	public function echo_addons_page( $addons_page_url, $addons ) {
		$addons = $this->set_addons_state( $addons );

		$context = [
			'addons_page_url' => $addons_page_url,
			'addons'          => (array) $addons,
		];

		echo $this->twig->render( 'addons_list.twig', $context );
	}

	/**
	 * @param array $addons
	 *
	 * @return array
	 */
	protected function set_addons_state( $addons ) {
		if ( ! empty( $addons ) ) {
			foreach ( $addons as $addon ) {
				$addon['installed'] = $this->is_plugin_installed( $addon['slug'] );
				$addon['active']    = false;

				if ( $addon['installed'] ) {
					$addon['active'] = $this->is_plugin_active( $addon['slug'] );
				}
			}
		}

		return $addons;
	}

	/**
	 * @param $plugin_name
	 *
	 * @return bool
	 */
	public function is_plugin_installed( $plugin_name ) {
		return file_exists( $this->plugin_dir_path . "/{$plugin_name}/{$plugin_name}.php" );
	}

	/**
	 * @param $plugin_name
	 *
	 * @return bool
	 */
	public function is_plugin_active( $plugin_name ) {
		return is_plugin_active( "{$plugin_name}/{$plugin_name}.php" );
	}
}
