<?php
/**
 * Runtime
 *
 * @package notification
 */

namespace BracketSpace\Notification;

use BracketSpace\Notification\Admin;
use BracketSpace\Notification\Core;
use BracketSpace\Notification\Vendor\Micropackage\DocHooks;
use BracketSpace\Notification\Vendor\Micropackage\Filesystem\Filesystem;
use BracketSpace\Notification\Vendor\Micropackage\Templates\Storage as TemplateStorage;

/**
 * Runtime class
 */
class Runtime extends DocHooks\HookAnnotations {

	/**
	 * Class constructor
	 *
	 * @since 5.0.0
	 * @param string $plugin_file plugin main file full path.
	 */
	public function __construct( $plugin_file ) {
		$this->plugin_file = $plugin_file;
	}

	/**
	 * Loads needed files
	 *
	 * @since  5.0.0
	 * @since  6.0.0 Added boot action.
	 * @since  [Next] All the defaults and boot action are called on initialization.
	 * @return void
	 */
	public function boot() {

		$this->filesystems();
		$this->templates();
		$this->singletons();
		$this->load_functions();
		$this->load_deprecated();
		$this->load_defaults();
		$this->actions();

		do_action_deprecated( 'notification/boot/initial', [], '[Next]', 'notification/boot' );
		do_action( 'notification/boot' );

	}

	/**
	 * Registers all the hooks with DocHooks
	 *
	 * @since  6.1.0
	 * @return void
	 */
	public function register_hooks() {

		$this->add_hooks();

		foreach ( get_object_vars( $this ) as $instance ) {
			if ( is_object( $instance ) ) {
				$this->add_hooks( $instance );
			}
		}

	}

	/**
	 * Sets up the templates storage
	 *
	 * @since  [Next]
	 * @return void
	 */
	public function templates() {

		TemplateStorage::add( 'templates', $this->get_filesystem( 'root' )->path( 'src/templates' ) );

	}

	/**
	 * Sets up the plugin filesystems
	 *
	 * @since  [Next]
	 * @return void
	 */
	public function filesystems() {

		$root = new Filesystem( dirname( $this->plugin_file ) );

		$this->filesystems = [
			'root'     => $root,
			'dist'     => new Filesystem( $root->path( 'dist' ) ),
			'includes' => new Filesystem( $root->path( 'src/includes' ) ),
		];

	}

	/**
	 * Gets filesystem
	 *
	 * @since  [Next]
	 * @param  string $name Filesystem name.
	 * @return Filesystem|null
	 */
	public function get_filesystem( $name ) {
		return $this->filesystems[ $name ];
	}

	/**
	 * Creates needed classes
	 * Singletons are used for a sake of performance
	 *
	 * @since  5.0.0
	 * @return void
	 */
	public function singletons() {

		$this->core_cron       = new Core\Cron();
		$this->core_whitelabel = new Core\Whitelabel();
		$this->core_debugging  = new Core\Debugging();
		$this->core_settings   = new Core\Settings();
		$this->core_upgrade    = new Core\Upgrade();
		$this->core_sync       = new Core\Sync();

		$this->admin_impexp     = new Admin\ImportExport();
		$this->admin_settings   = new Admin\Settings();
		$this->admin_duplicator = new Admin\NotificationDuplicator();
		$this->admin_post_type  = new Admin\PostType();
		$this->admin_post_table = new Admin\PostTable();
		$this->admin_extensions = new Admin\Extensions();
		$this->admin_scripts    = new Admin\Scripts( $this, $this->get_filesystem( 'dist' ) );
		$this->admin_screen     = new Admin\Screen();
		$this->admin_wizard     = new Admin\Wizard( $this->get_filesystem( 'includes' ) );
		$this->admin_sync       = new Admin\Sync();
		$this->admin_debugging  = new Admin\Debugging();

		$this->integration_wp        = new Integration\WordPress();
		$this->integration_wp_emails = new Integration\WordPressEmails();
		$this->integration_gb        = new Integration\Gutenberg();
		$this->integration_cf        = new Integration\CustomFields();
		$this->integration_bp        = new Integration\BackgroundProcessing();
		$this->integration_mce       = new Integration\TinyMce();
	}

	/**
	 * All WordPress actions this plugin utilizes
	 *
	 * @since  5.0.0
	 * @return void
	 */
	public function actions() {

		$this->register_hooks();

		notification_register_settings( [ $this->admin_settings, 'general_settings' ] );
		notification_register_settings( [ $this->admin_settings, 'triggers_settings' ], 20 );
		notification_register_settings( [ $this->admin_settings, 'notifications_settings' ], 30 );
		notification_register_settings( [ $this->admin_settings, 'emails_settings' ], 40 );
		notification_register_settings( [ $this->admin_sync, 'settings' ], 50 );
		notification_register_settings( [ $this->admin_impexp, 'settings' ], 60 );
		notification_register_settings( [ $this->admin_debugging, 'debugging_settings' ], 70 );

		register_uninstall_hook( $this->plugin_file, [ 'BracketSpace\Notification\Core\Uninstall', 'remove_plugin_data' ] );

		// DocHooks compatibility.
		if ( ! DocHooks\Helper::is_enabled() && $this->get_filesystem( 'includes' )->exists( 'hooks.php' ) ) {
			include_once $this->get_filesystem( 'includes' )->path( 'hooks.php' );
		}

	}

	/**
	 * Loads functions
	 *
	 * @since  6.0.0
	 * @return void
	 */
	public function load_functions() {

		$function_files = [
			'general',
			'settings',
			'resolver',
			'carrier',
			'trigger',
			'recipient',
			'notification',
			'notification-post',
			'whitelabel',
			'import-export',
			'adapter',
		];

		array_map( function( $function_file ) {
				require_once $this->get_filesystem( 'includes' )->path( sprintf( 'functions/%s.php', $function_file ) );
		}, $function_files );

	}

	/**
	 * Loads deprecated functions and classes
	 *
	 * @since  6.0.0
	 * @return void
	 */
	public function load_deprecated() {

		$deprecation_files = [
			// Functions.
			'functions',
			// Classes.
			'class/Abstracts/Notification',
			'class/Defaults/Notification/Email',
			'class/Defaults/Notification/Webhook',
		];

		array_map( function( $deprecation_file ) {
				require_once $this->get_filesystem( 'includes' )->path( sprintf( 'deprecated/%s.php', $deprecation_file ) );
		}, $deprecation_files );

	}

	/**
	 * Loads defaults
	 *
	 * @since  6.0.0
	 * @return void
	 */
	public function load_defaults() {
		array_map( [ $this, 'load_default' ], [
			'global-merge-tags',
			'resolvers',
			'recipients',
			'carriers',
			'triggers',
		] );
	}

	/**
	 * Loads default
	 *
	 * @since  6.0.0
	 * @param  string $default Default file slug.
	 * @return void
	 */
	public function load_default( $default ) {
		if ( apply_filters( 'notification/load/default/' . $default, true ) ) {
			$path = sprintf( 'defaults/%s.php', $default );
			if ( $this->get_filesystem( 'includes' )->exists( $path ) ) {
				require_once $this->get_filesystem( 'includes' )->path( $path );
			}
		}
	}

}
