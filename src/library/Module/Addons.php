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
	 * @var array
	 */
	protected $addons;

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
		add_action( 'alex_echo_addons_page', [ $this, 'echo_addons_page' ], 10 );
		add_action( 'wp_ajax_allex_addon_license_validate', [ $this, 'ajax_validate_license_key' ] );
	}

	/**
	 * @param string $addons_page_url
	 *
	 * @throws \Twig_Error_Loader
	 * @throws \Twig_Error_Runtime
	 * @throws \Twig_Error_Syntax
	 */
	public function echo_addons_page( $addons_page_url ) {
		/**
		 *     $addons = [
		 *          [
		 *              'slug'        => '',
		 *              'title'       => '',
		 *              'description' => '',
		 *              'icon'        => '',
		 *          ],
		 *     ],
		 */
		$addons = apply_filters( 'allex_addons', $this->plugin_name, [] );

		$count = $this->split_addons_by_state( $addons );

		$this->check_addons_license( $addons );

		// Cache the add-ons list.
		$this->addons = $addons;

		$context = [
			'addons_page_url' => $addons_page_url,
			'addons'          => $addons,
			'count_addons'    => $count,
			'plugin_name'     => $this->plugin_name,
			'nonce'           => wp_create_nonce( 'allex_activate_license' ),
			'labels'          => [
				'installed'         => __( 'Installed Extensions', 'allex' ),
				'browse_more'       => __( 'Browse More Extensions', 'allex' ),
				'enter_license_key' => __( 'Enter your license key', 'allex' ),
				'activate'          => __( 'Activate', 'allex' ),
				'license_key'       => __( 'License Key', 'allex' ),
				'change'            => __( 'Change', 'allex' ),
				'get_plugins'       => __( 'Get Pro Add-ons!', 'allex' ),
			],
		];

		echo $this->twig->render( 'addons_list.twig', $context );
	}

	/**
	 * Split the $addons array into a multidimensional array which indexes
	 * specify the current state of the plugins. It returns the total of
	 * add-ons.
	 *
	 * @param array $addons
	 *
	 * @return integer
	 */
	protected function split_addons_by_state( &$addons ) {
		$new_list = [
			'missed '   => [],
			'installed' => [],
		];

		$count = 0;

		if ( ! empty( $addons ) ) {
			foreach ( $addons as &$addon ) {
				$index = 'missed';

				if ( $this->is_plugin_installed( $addon['slug'] ) ) {
					$index = 'installed';

					$addon['active'] = $this->is_plugin_active( $addon['slug'] );
				}

				$new_list[ $index ][] = $addon;

				$count ++;
			}
		}

		$addons = $new_list;

		return $count;
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


	/**
	 * @param $addons
	 */
	protected function check_addons_license( &$addons ) {
		if ( empty( $addons ) ) {
			return;
		}

		foreach ( $addons as &$addon ) {
			$addon['license_status'] = 'inactive';
			$addon['license_key']    = '';
		}
	}

	/**
	 * AJAX endpoint that validates a given license key for an extension.
	 * Echoes JSON data.
	 */
	public function ajax_validate_license_key() {
		header( 'Content-Type: application/json' );

		/**
		 *     $addons = [
		 *          [
		 *              'slug'        => '',
		 *              'title'       => '',
		 *              'description' => '',
		 *              'icon'        => '',
		 *          ],
		 *     ],
		 */
		$addons = apply_filters( 'allex_addons', $this->plugin_name, [] );

		$response = [
			'success'        => false,
			'message'        => '',
			'license_status' => '',
			'license_key'    => '',
		];

		try {
			if ( ! current_user_can( 'activate_plugins' ) ) {
				throw new \Exception( __( "You're not allowed to do this.", 'allex' ) );
			}

			if ( empty( $_POST ) || ! isset( $_POST['key'] ) || ! isset( $_POST['plugin_name'] ) || ! isset( $_POST['nonce'] ) ) {
				throw new \Exception( __( 'Invalid request.', 'allex' ) );
			}

			if ( ! check_ajax_referer( 'allex_activate_license', 'nonce', false ) ) {
				throw new \Exception( __( "You're not allowed to do this.", 'allex' ) );
			}

			$plugin_name = sanitize_text_field( trim( $_POST['plugin_name'] ) );
			if ( empty( $plugin_name ) ) {
				throw new \Exception( __( 'Invalid plugin name.', 'allex' ) );
			}

			$addon_name = sanitize_text_field( trim( $_POST['addon_name'] ) );
			if ( empty( $addon_name ) ) {
				throw new \Exception( __( 'Invalid addon name.', 'allex' ) );
			}

			$license_key = sanitize_text_field( trim( $_POST['key'] ) );
			if ( empty( $license_key ) ) {
				throw new \Exception( __( 'Invalid license key.', 'allex' ) );
			}

			if ( empty( $addons ) ) {
				throw new \Exception( __( "Invalid add-on.", 'allex' ) );
			}

			if ( ! array_key_exists( $addon_name, $addons ) ) {
				throw new \Exception( __( 'Invalid addon name.', 'allex' ) );
			}

			$response['message'] = 'Ok!';
			$response['success'] = true;
			$response['license_key'] = $license_key;
		} catch ( \Exception $e ) {
			$response['message'] = $e->getMessage();
		}

		echo wp_json_encode( $response );

		wp_die();
	}
}
