<?php
/**
 * WP to Buffer class.
 *
 * @package WP_To_Buffer_Pro
 * @author WP Zinc
 */

/**
 * Main WP to Buffer class, used to load the Plugin.
 *
 * @package   WP_To_Buffer
 * @author    WP Zinc
 * @version   1.0.0
 */
class WP_To_Buffer {

	/**
	 * Holds the class object.
	 *
	 * @since   3.1.4
	 *
	 * @var     object
	 */
	public static $instance;

	/**
	 * Plugin
	 *
	 * @since   3.0.0
	 *
	 * @var     object
	 */
	public $plugin = '';

	/**
	 * Dashboard
	 *
	 * @since   3.1.4
	 *
	 * @var     object
	 */
	public $dashboard = '';

	/**
	 * Classes
	 *
	 * @since   3.4.9
	 *
	 * @var     array
	 */
	public $classes = '';

	/**
	 * Constructor. Acts as a bootstrap to load the rest of the plugin
	 *
	 * @since   1.0.0
	 */
	public function __construct() {

		// Plugin Details.
		$this->plugin              = new stdClass();
		$this->plugin->name        = 'wp-to-buffer';
		$this->plugin->filter_name = 'wp_to_buffer';
		$this->plugin->displayName = 'WP to Buffer';

		$this->plugin->settingsName      = 'wp-to-buffer-pro'; // Settings key - used in both Free + Pro, and for oAuth.
		$this->plugin->account           = 'Buffer';
		$this->plugin->version           = WP_TO_BUFFER_PLUGIN_VERSION;
		$this->plugin->buildDate         = WP_TO_BUFFER_PLUGIN_BUILD_DATE;
		$this->plugin->folder            = WP_TO_BUFFER_PLUGIN_PATH;
		$this->plugin->url               = WP_TO_BUFFER_PLUGIN_URL;
		$this->plugin->documentation_url = 'https://www.wpzinc.com/documentation/wordpress-buffer-pro';
		$this->plugin->support_url       = 'https://www.wpzinc.com/support';
		$this->plugin->upgrade_url       = 'https://www.wpzinc.com/plugins/wordpress-to-buffer-pro';
		$this->plugin->logo              = WP_TO_BUFFER_PLUGIN_URL . 'lib/assets/images/icons/buffer-dark.svg';
		$this->plugin->review_name       = 'wp-to-buffer';

		// Defer loading of Plugin Classes.
		add_action( 'init', array( $this, 'initialize' ), 1 );
		add_action( 'init', array( $this, 'upgrade' ), 2 );

		// Admin Menus.
		add_action( $this->plugin->filter_name . '_admin_admin_menu', array( $this, 'admin_menus' ) );

	}

	/**
	 * Register menus and submenus.
	 *
	 * @since   3.9.7
	 *
	 * @param   string $minimum_capability     Minimum required capability.
	 */
	public function admin_menus( $minimum_capability ) {

		// Menus.
		add_menu_page( $this->plugin->displayName, $this->plugin->displayName, $minimum_capability, $this->plugin->name . '-settings', array( $this->get_class( 'admin' ), 'settings_screen' ), $this->plugin->url . 'lib/assets/images/icons/' . strtolower( $this->plugin->account ) . '-light.svg' );

		// Register Submenu Pages.
		$settings_page = add_submenu_page( $this->plugin->name . '-settings', __( 'Settings', 'wp-to-buffer' ), __( 'Settings', 'wp-to-buffer' ), $minimum_capability, $this->plugin->name . '-settings', array( $this->get_class( 'admin' ), 'settings_screen' ) );

		// Logs.
		if ( $this->get_class( 'log' )->is_enabled() ) {
			$log_page = add_submenu_page( $this->plugin->name . '-settings', __( 'Logs', 'wp-to-buffer' ), __( 'Logs', 'wp-to-buffer' ), $minimum_capability, $this->plugin->name . '-log', array( $this->get_class( 'admin' ), 'log_screen' ) );
			add_action( "load-$log_page", array( $this->get_class( 'log' ), 'add_screen_options' ) );
		}

		$upgrade_page = add_submenu_page( $this->plugin->name . '-settings', __( 'Upgrade', 'wp-to-buffer' ), __( 'Upgrade', 'wp-to-buffer' ), $minimum_capability, $this->plugin->name . '-upgrade', array( $this->get_class( 'admin' ), 'upgrade_screen' ) );

	}

	/**
	 * Initializes required classes
	 *
	 * @since   3.4.9
	 */
	public function initialize() {

		// Define translation strings.
		$this->plugin->review_notice = sprintf(
			/* translators: Plugin Name */
			__( 'Thanks for using %s to schedule your social media statuses on Buffer!', 'wp-to-buffer' ),
			$this->plugin->displayName
		);

		// ConvertKit Form UID.
		$this->plugin->convertkit_form_uid = '71346c6086';

		// Default Settings.
		$this->plugin->default_schedule = 'queue_bottom';

		// Upgrade Reasons.
		$this->plugin->upgrade_reasons = array(
			array(
				__( 'Post to Instagram and Pinterest', 'wp-to-buffer' ),
				__( 'Pro supports Direct Posting to Instagram Business Profiles and Pinterest Boards', 'wp-to-buffer' ),
			),
			array(
				__( 'Multiple Buffer Account Support', 'wp-to-buffer' ),
				__( 'Pro supports connecting multiple Buffer accounts to a single WordPress site', 'wp-to-buffer' ),
			),
			array(
				__( 'Multiple, Customisable Status Messages', 'wp-to-buffer' ),
				__( 'Each Post Type and Social Network can have multiple, unique status message and settings', 'wp-to-buffer' ),
			),
			array(
				__( 'Conditionally send Status Messages', 'wp-to-buffer' ),
				__( 'Only send status(es) to Buffer based on Post Author(s), Taxonomy Term(s) and/or Custom Field Values', 'wp-to-buffer' ),
			),
			array(
				__( 'More Scheduling Options', 'wp-to-buffer' ),
				__( 'Each status update can be added to the start/end of your Buffer queue, posted immediately or scheduled at a specific time', 'wp-to-buffer' ),
			),
			array(
				__( 'Dynamic Status Tags', 'wp-to-buffer' ),
				__( 'Dynamically build status updates with data from the Post Author and Custom Fields', 'wp-to-buffer' ),
			),
			array(
				__( 'Separate Statuses per Social Network', 'wp-to-buffer' ),
				__( 'Define different statuses for each Post Type and Social Network', 'wp-to-buffer' ),
			),
			array(
				__( 'Per-Post Settings', 'wp-to-buffer' ),
				__( 'Override Settings on Individual Posts: Each Post can have its own Buffer settings', 'wp-to-buffer' ),
			),
			array(
				__( 'Repost Old Posts', 'wp-to-buffer' ),
				__( 'Automatically Revive Old Posts that haven\'t been updated in a while, choosing the number of days, weeks or years to re-share content on social media.', 'wp-to-buffer' ),
			),
			array(
				__( 'Bulk Publish Old Posts', 'wp-to-buffer' ),
				__( 'Manually re-share evergreen WordPress content and revive old posts with the Bulk Publish option', 'wp-to-buffer' ),
			),
			array(
				__( 'The Events Calendar, Event Manager and Modern Events Calendar Integration', 'wp-to-buffer' ),
				__( 'Schedule Posts to Buffer based on your Event\'s Start or End date, and display Event-specific details in your status updates', 'wp-to-buffer' ),
			),
			array(
				__( 'SEO Integration', 'wp-to-buffer' ),
				__( 'Display SEO-specific information in your status updates from All-In-One SEO Pack, Rank Math, SEOPress and Yoast SEO', 'wp-to-buffer' ),
			),
			array(
				__( 'WooCommerce Integration', 'wp-to-buffer' ),
				__( 'Display Product-specific information in your status updates', 'wp-to-buffer' ),
			),
			array(
				__( 'Autoblogging and Frontend Post Submission Integration', 'wp-to-buffer' ),
				__( 'Pro supports autoblogging and frontend post submission Plugins, including User Submitted Posts, WP Property Feed, WPeMatico and WP Job Manager', 'wp-to-buffer' ),
			),
			array(
				__( 'Shortcode Support', 'wp-to-buffer' ),
				__( 'Use shortcodes in status updates', 'wp-to-buffer' ),
			),
			array(
				__( 'Full Image Control', 'wp-to-buffer' ),
				__( 'Choose to display one or more images in your status updates, from the Post\'s Featured Image, the Media Gallery, the Post Content or an Advanced Custom Fields Image or Gallery.', 'wp-to-buffer' ),
			),
			array(
				__( 'WP-Cron and WP-CLI Compatible', 'wp-to-buffer' ),
				__( 'Optionally enable WP-Cron to send status updates via Cron, speeding up UI performance and/or choose to use WP-CLI for reposting old posts', 'wp-to-buffer' ),
			),
		);

		// Dashboard Submodule.
		if ( ! class_exists( 'WPZincDashboardWidget' ) ) {
			require_once $this->plugin->folder . '_modules/dashboard/class-wpzincdashboardwidget.php';
		}
		$this->dashboard = new WPZincDashboardWidget( $this->plugin, 'https://www.wpzinc.com/wp-content/plugins/lum-deactivation' );

		// Initialize Plugin classes.
		$this->classes = new stdClass();

		// Initialize required classes.
		$this->classes->admin         = new WP_To_Social_Pro_Admin( self::$instance );
		$this->classes->ajax          = new WP_To_Social_Pro_AJAX( self::$instance );
		$this->classes->api           = new WP_To_Social_Pro_Buffer_API( self::$instance );
		$this->classes->common        = new WP_To_Social_Pro_Common( self::$instance );
		$this->classes->cron          = new WP_To_Social_Pro_Cron( self::$instance );
		$this->classes->date          = new WP_To_Social_Pro_Date( self::$instance );
		$this->classes->image         = new WP_To_Social_Pro_Image( self::$instance );
		$this->classes->install       = new WP_To_Social_Pro_Install( self::$instance );
		$this->classes->log           = new WP_To_Social_Pro_Log( self::$instance );
		$this->classes->media_library = new WP_To_Social_Pro_Media_Library( self::$instance );
		$this->classes->notices       = new WP_To_Social_Pro_Notices( self::$instance );
		$this->classes->post          = new WP_To_Social_Pro_Post( self::$instance );
		$this->classes->publish       = new WP_To_Social_Pro_Publish( self::$instance );
		$this->classes->screen        = new WP_To_Social_Pro_Screen( self::$instance );
		$this->classes->settings      = new WP_To_Social_Pro_Settings( self::$instance );
		$this->classes->twitter_api   = new WP_To_Social_Pro_Twitter_API( self::$instance );
		$this->classes->validation    = new WP_To_Social_Pro_Validation( self::$instance );

	}

	/**
	 * Runs the upgrade routine once the plugin has loaded
	 *
	 * @since   3.2.5
	 */
	public function upgrade() {

		// Run upgrade routine.
		$this->get_class( 'install' )->upgrade();

	}

	/**
	 * Returns the given class
	 *
	 * @since   3.4.9
	 *
	 * @param   string $name   Class Name.
	 */
	public function get_class( $name ) {

		// If the class hasn't been loaded, throw a WordPress die screen
		// to avoid a PHP fatal error.
		if ( ! isset( $this->classes->{ $name } ) ) {
			// Define the error.
			$error = new WP_Error(
				'wp_to_buffer_get_class',
				sprintf(
					/* translators: %1$s: Plugin Name, %2$s: PHP class name */
					__( '%1$s: Error: Could not load Plugin class %2$s', 'wp-to-buffer' ),
					$this->plugin->displayName,
					$name
				)
			);

			// Depending on the request, return or display an error.
			// Admin UI.
			if ( is_admin() ) {
				wp_die(
					esc_html( $error->get_error_message() ),
					sprintf(
						/* translators: Plugin Name */
						esc_html__( '%s: Error', 'wp-to-buffer' ),
						esc_html( $this->plugin->displayName )
					),
					array(
						'back_link' => true,
					)
				);
			}

			// Cron / CLI.
			return $error;
		}

		// Return the class object.
		return $this->classes->{ $name };

	}

	/**
	 * Helper method to determine whether this Plugin supports a specific feature.
	 *
	 * Typically used by the lib/ classes.
	 *
	 * @since   3.5.5
	 *
	 * @param   string $feature    Feature.
	 * @return  bool                Feature Supported
	 */
	public function supports( $feature ) {

		// Define supported featured.
		$supported_features = array(
			'webp',
		);

		return in_array( $feature, $supported_features, true );

	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since   3.1.4
	 *
	 * @return  object Class.
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) {
			self::$instance = new self();
		}

		return self::$instance;

	}

}
