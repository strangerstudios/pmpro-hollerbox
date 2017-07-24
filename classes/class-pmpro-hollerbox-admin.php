<?php

/**
 * Class for PMPro Holler Box (Admin)
 * All admin related code to be added to this class.
 */

if( !defined( 'ABSPATH' ) ) exit;

require_once 'class-tgm-plugin-activation.php';

class PMPro_Hollerbox_Admin{

	private static $instance;

	/**
     * Creates or returns an instance of this class.
     *
     * @return  PMPro_Hollerbox A single instance of this class.
     */
	public static function get_instance() {

		if ( null == self::$instance ) {
		    self::$instance = new self;
		    self::$instance->hooks();
		}

	return self::$instance;
 
    } // end get_instance;

 	/**
	 * Run action and filter hooks
	 *
	 * @access      private
	 * @since       0.1
	 * @return      void
	 */
	private function hooks() {
		add_action( 'tgmpa_register', array( $this, 'show_required_plugins_warning' ) );
	}

	/**
	 * Show warning message for required plugins.
	 * Leverages TGM library - look at class-tgm-plugin-activation.php
	 *
	 * @since 0.1
	 * @return void
	 */
	public function show_required_plugins_warning() {
		/*
		 * Array of plugin arrays. Required keys are name and slug.
		 * If the source is NOT from the .org repo, then source is also required.
		 */
		$plugins = array(

			//
			array(
				'name' => 'Holler Box',
				'slug' => 'holler-box',
				'required' => true,
			),

			array(
				'name' => 'Paid Memberships Pro',
				'slug' => 'paid-memberships-pro',
				'required' => true,
			),

		);
		
		/**
		 * Array of configuration settings. Amend each line as needed.
		 *
		 */
		$config = array(
			'id'           => 'pmpro-hollerbox',                 // Unique ID for hashing notices for multiple instances of TGMPA.
			'default_path' => '',                      // Default absolute path to bundled plugins.
			'menu'         => 'pmpro-install-plugins', // Menu slug.
			'parent_slug'  => 'plugins.php',            // Parent menu slug.
			'capability'   => 'manage_options',    // Capability needed to view plugin install page, should be a capability associated with the parent menu used.
			'has_notices'  => true,                    // Show admin notices or not.
			'dismissable'  => true,                    // If false, a user cannot dismiss the nag message.
			'dismiss_msg'  => '',                      // If 'dismissable' is false, this message will be output at top of nag.
			'is_automatic' => false,                   // Automatically activate plugins after installation or not.
			'message'      => '',                      // Message to output right before the plugins table.

			
			'strings'      => array(
				'page_title'                      => __( 'Install Required Plugins For PMPro Hollerbox Sales', 'pmpro-hollerbox' ),
				'menu_title'                      => __( 'Required Plugins', 'pmpro-hollerbox' ),
				/* translators: %s: plugin name. */
				'installing'                      => __( 'Installing Plugin: %s', 'pmpro-hollerbox' ),
				/* translators: %s: plugin name. */
				'updating'                        => __( 'Updating Plugin: %s', 'pmpro-hollerbox' ),
				'oops'                            => __( 'Something went wrong with the plugin API.', 'pmpro-hollerbox' ),
				'notice_can_install_required'     => _n_noop(
					/* translators: 1: plugin name(s). */
					'PMPro Hollerbox Sales Notifications requires the following plugin: %1$s.',
					'PMPro Hollerbox Sales Notifications requires the following plugins: %1$s.',
					'pmpro-hollerbox'
				),
				'notice_can_install_recommended'  => _n_noop(
					/* translators: 1: plugin name(s). */
					'PMPro Hollerbox Sales Notifications recommends the following plugin: %1$s.',
					'PMPro Hollerbox Sales Notifications recommends the following plugins: %1$s.',
					'pmpro-hollerbox'
				),
				'notice_ask_to_update'            => _n_noop(
					/* translators: 1: plugin name(s). */
					'The following plugin needs to be updated to its latest version to ensure maximum compatibility with PMPro Hollerbox Sales Notifications: %1$s.',
					'The following plugins need to be updated to their latest version to ensure maximum compatibility with PMPro Hollerbox Sales Notifications: %1$s.',
					'pmpro-hollerbox'
				),
				'notice_ask_to_update_maybe'      => _n_noop(
					/* translators: 1: plugin name(s). */
					'There is an update available for: %1$s.',
					'There are updates available for the following plugins: %1$s.',
					'pmpro-hollerbox'
				),
				'notice_can_activate_required'    => _n_noop(
					/* translators: 1: plugin name(s). */
					'The following required plugin is currently inactive: %1$s.',
					'The following required plugins are currently inactive: %1$s.',
					'pmpro-hollerbox'
				),
				'notice_can_activate_recommended' => _n_noop(
					/* translators: 1: plugin name(s). */
					'The following recommended plugin is currently inactive: %1$s.',
					'The following recommended plugins are currently inactive: %1$s.',
					'pmpro-hollerbox'
				),
				'install_link'                    => _n_noop(
					'Begin installing plugin',
					'Begin installing plugins',
					'pmpro-hollerbox'
				),
				'update_link' 					  => _n_noop(
					'Begin updating plugin',
					'Begin updating plugins',
					'pmpro-hollerbox'
				),
				'activate_link'                   => _n_noop(
					'Begin activating plugin',
					'Begin activating plugins',
					'pmpro-hollerbox'
				),
				'return'                          => __( 'Return to Required Plugins Installer', 'pmpro-hollerbox' ),
				'plugin_activated'                => __( 'Plugin activated successfully.', 'pmpro-hollerbox' ),
				'activated_successfully'          => __( 'The following plugin was activated successfully:', 'pmpro-hollerbox' ),
				/* translators: 1: plugin name. */
				'plugin_already_active'           => __( 'No action taken. Plugin %1$s was already active.', 'pmpro-hollerbox' ),
				/* translators: 1: plugin name. */
				'plugin_needs_higher_version'     => __( 'Plugin not activated. A higher version of %s is needed for PMPro Hollerbox Sales Notifications. Please update the plugin.', 'pmpro-hollerbox' ),
				/* translators: 1: dashboard link. */
				'complete'                        => __( 'All plugins installed and activated successfully. %1$s', 'pmpro-hollerbox' ),
				'dismiss'                         => __( 'Dismiss this notice', 'pmpro-hollerbox' ),
				'notice_cannot_install_activate'  => __( 'There are one or more required or recommended plugins to install, update or activate.', 'pmpro-hollerbox' ),
				'contact_admin'                   => __( 'Please contact the administrator of this site for help.', 'pmpro-hollerbox' ),

				'nag_type'                        => 'notice-warning', // Determines admin notice type - can only be one of the typical WP notice classes, such as 'updated', 'update-nag', 'notice-warning', 'notice-info' or 'error'. Some of which may not work as expected in older WP versions.
			),
			
		);
		tgmpa( $plugins, $config );
	}
	
} //class ends

$pmpro_holler_admin = new PMPro_Hollerbox_Admin();
$pmpro_holler_admin->get_instance();