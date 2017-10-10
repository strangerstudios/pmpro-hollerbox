<?php
/**
 * Plugin Name: Paid Memberships Pro - Holler Box Integration
 * Description: Integrates Paid Memberships Pro with the Holler Box plugin to display popups/banners by membership level.
 * Plugin URI: https://www.paidmembershipspro.com/add-ons/holler-box-integration/
 * Author: Paid Memberships Pro
 * Author URI: https://www.paidmembershipspro.com
 * Version: .1.1
 * License: GPL2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: pmpro-hollerbox
 */

defined( 'ABSPATH' ) or exit;

class PMPro_Hollerbox {
 
    /** Refers to a single instance of this class. */
    private static $instance = null;
 
    /**
     * Creates or returns an instance of this class.
     *
     * @return  PMPro_Hollerbox A single instance of this class.
     */
    public static function get_instance() {
 
        if ( null == self::$instance ) {
            self::$instance = new self;
            self::$instance->includes();
        }
 
        return self::$instance;
 
    } // end get_instance;
 
    /**
     * Initializes the plugin by setting localization, filters, and administration functions.
     */
    private function __construct() {

    	//initialize the plugin & check for required plugins
    	add_action( 'init', array( $this, 'init' ) );
    } // end constructor

    /**
     * Add all included classes/files inside this method.
     * 
     * @access private
     * @since 0.1
     * @return void
     */
    private static function includes(){
 		
 		if( is_admin() ) {
			require_once dirname( __FILE__ ) . '/classes/class-pmpro-hollerbox-admin.php';
 		}

    }
   
    public static function init() {
    	// Settings added to Holler Box advanced settings.
    	add_action( 'hwp_advanced_settings_after', array( 'PMPro_Hollerbox', 'hwp_advanced_settings_after' ) );
    	add_filter( 'hwp_settings_array', array( 'PMPro_Hollerbox', 'hwp_settings_array' ) );

    	// Filter to display the Holler Box according to membership level.
    	add_filter( 'hwp_display_notification', array( 'PMPro_Hollerbox', 'hwp_display_notification' ), 10, 3 );

    }


	/**
	 * Add our own metakey for post meta.
	 *
	 * @since 0.1
	 * @return array
	 */
	public static function hwp_settings_array( $keys ){

		$pmpro_settings_array = array();

		// add option for non-members
		$keys[] = 'pmpro_membership_no_level';

		$levels = pmpro_getAllLevels( true, true );

		//save each level as post meta.
		foreach ($levels as $key => $value) {
			$keys[] = 'pmpro_membership_level_' . $value->id;
		}
		
		return $keys;

	}

	/**
	 * Add our own settings to each Holler Box (Under 'Advanced Settings')
	 *
	 * @since 0.1
	 * @return void
	 */
	public static function hwp_advanced_settings_after( $post_id ){
		//get all pmpro levels
		$levels = pmpro_getAllLevels( true, true );

		$non_member_saved = get_post_meta( $post_id, 'pmpro_membership_no_level', true );
		?>
		</div>
		<div class="hwp-section noborder">
			<hr />
			<h3>Membership Settings</h3>
			<p><label for="pmpro_membership_levels"><?php _e( 'Show this Holler Box for the following membership levels', 'pmpro-hollerbox' ); ?></p></label>
			<div class="hwp-settings-group">
				<input type="checkbox" name="pmpro_membership_no_level" value="non_member" <?php checked( $non_member_saved, 'non_member', true );?>><?php _e( 'Non-Members (including logged-in, non-members)', 'pmpro-hollerbox' ); ?></input><br>
				<?php foreach( $levels as $key => $value ) { 
					//get_saved_levels
					$levels_saved = get_post_meta( $post_id, 'pmpro_membership_level_'.$value->id, true ); ?>
					<input type="checkbox" name="pmpro_membership_level_<?php echo $value->id; ?>" value="<?php echo $value->id; ?>" <?php if( !empty( $levels_saved ) ){ ?> checked <?php } ?>><?php echo $value->name ?></input><br />
				<?php } ?>
			</div>
            <hr />
		<?php
	}

	/**
	 * Displays a Holler Box depending on membership level.
	 *
	 * @since 0.1
	 * @return boolean
	 */
	public static function hwp_display_notification( $show_it, $box_id, $post_id ){

		$levels = pmpro_getAllLevels( true, true );

		$membership_level_required = array();

		// Create an array for which levels to show the Holler Box for.
		foreach ($levels as $key => $value) {
			$membership_level_required[] = get_post_meta( $box_id, 'pmpro_membership_level_'.$value->id , true );
		}
		
		if ( !empty( $membership_level_required ) ) {
				if ( pmpro_hasMembershipLevel( $membership_level_required ) ) {
					$show_it = true;
				}else{
					$show_it = false;
				}
			}

		// Check to see if non-members is selected.
		$non_members = get_post_meta( $box_id, 'pmpro_membership_no_level', true );

		// Check to see if non_members data exists and user does not have membership level.
		if( !empty( $non_members ) ) {
			if( !pmpro_hasMembershipLevel() ) {
				$show_it = true;
			}
		}

		return $show_it;
	}

} // end class


/**
 * The main function responsible for returning the one true Holler Box for PMPro
 * instance to functions everywhere
 *
 * @since       0.1.0
 * @return      PMPro_Hollerbox::get_instance()
 *
 */
function pmpro_hollerbox_load() {
    return PMPro_Hollerbox::get_instance();
}
add_action( 'plugins_loaded', 'pmpro_hollerbox_load' );

/*
	Function to add links to the plugin row meta
*/
function pmpro_hollerbox_plugin_row_meta($links, $file) {
	if(strpos($file, 'pmpro-hollerbox.php') !== false)
	{
		$new_links = array(
			'<a href="' . esc_url('https://www.paidmembershipspro.com/add-ons/holler-box-integration/')  . '" title="' . esc_attr( __( 'View Documentation', 'pmpro-hollerbox' ) ) . '">' . __( 'Docs', 'pmpro-hollerbox' ) . '</a>',
			'<a href="' . esc_url('http://paidmembershipspro.com/support/') . '" title="' . esc_attr( __( 'Visit Customer Support Forum', 'pmpro-hollerbox' ) ) . '">' . __( 'Support', 'pmpro-hollerbox' ) . '</a>',
		);
		$links = array_merge($links, $new_links);
	}
	return $links;
}
add_filter( 'plugin_row_meta', 'pmpro_hollerbox_plugin_row_meta', 10, 2 );
