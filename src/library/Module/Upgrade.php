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
	 * @var string
	 */
	protected $plugin_title;

	/**
	 * @var string
	 */
	protected $subscription_ad_url;

	/**
	 * @var \Twig_Environment
	 */
	protected $twig;

	/**
	 * @var int
	 */
	protected $subscription_discount = 20;

	/**
	 * Upgrade constructor.
	 *
	 * @param Container $container
	 */
	public function __construct( Container $container ) {
		parent::__construct( $container );

		$this->plugin_basename     = $this->container['PLUGIN_BASENAME'];
		$this->plugin_name         = $this->container['PLUGIN_NAME'];
		$this->plugin_title        = $this->container['PLUGIN_TITLE'];
		$this->subscription_ad_url = $this->container['SUBSCRIPTION_AD_URL'];
		$this->twig                = $this->container['twig'];
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
		add_action( 'allex_upgrade_sidebar_ad', [ $this, 'render_sidebar_ad' ] );
		add_filter( 'allex_upgrade_show_sidebar_ad', [ $this, 'filter_allex_upgrade_show_sidebar_ad' ] );
	}

	/**
	 * @param array $links
	 *
	 * @return array
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

	/**
	 * Echo the sidebar with a form to subscribe for 20% discount.
	 */
	public function render_sidebar_ad() {
		$img_path = str_replace( ABSPATH, '', dirname( dirname( __DIR__ ) ) ) . '/assets/img/subscription-ad.jpg';

		echo $this->twig->render(
			'subscription_ad.twig',
			[
				'image_src'  => get_site_url() . '/' . $img_path,
				'action_url' => $this->subscription_ad_url,
				'text'       => [
					'title'         => sprintf( __( 'Get %d%% off the %s extensions', 'allex' ),
						$this->subscription_discount,
						$this->plugin_title ),
					'thanks'        => sprintf( __( 'Thanks for using %1$s! Enter your details and we\'ll send you a coupon for %2$d%% off the %1$s extensions.',
						'allex' ), $this->plugin_title, $this->subscription_discount ),
					'email_address' => __( 'Email Address', 'allex' ),
					'first_name'    => __( 'First Name', 'allex' ),
					'last_name'     => __( 'Last Name', 'allex' ),
					'yes_send_me'   => __( 'Yes! Send me the coupon', 'allex' ),
				],
			]
		);
	}

	/**
	 * @param $show_sidebar
	 *
	 * @return bool
	 */
	public function filter_allex_upgrade_show_sidebar_ad( $show_sidebar ) {
		if ( ( defined( 'DOING_AJAX' ) && DOING_AJAX )
		     || ( defined( 'DOING_CRON' ) && DOING_CRON )
		     || ! is_admin() ) {

			return false;
		}

		// Check if we have all add-ons installed. If so, we do not show the sidebar.
		$addons           = apply_filters( 'allex_addons', [], $this->plugin_name );
		$addons_installed = apply_filters( 'allex_installed_addons', [], $this->plugin_name );

		return count( $addons ) > count( $addons_installed );
	}
}
