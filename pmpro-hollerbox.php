<?php
/**
 * Plugin Name: Paid Memberships Pro - Hollerbox Sales Notifications
 * Description: Display a hollerbox message everytime someone checks out.
 * Plugin URI: https://paidmembershipspro.com
 * Author: Stranger Studios
 * Author URI: https://paidmembershipspro.com
 * Version: .1
 * License: GPL2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: pmpro-hollerbox
 */

defined( 'ABSPATH' ) or exit;




class PMPro_Hollerbox {

    /*--------------------------------------------*
     * Attributes
     *--------------------------------------------*/
 
    /** Refers to a single instance of this class. */
    private static $instance = null;
 
    /*--------------------------------------------*
     * Constructor
     *--------------------------------------------*/
 
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

    /*--------------------------------------------*
     * Functions
     *--------------------------------------------*/

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
    	//Holler Box hooks
    	add_action( 'hwp_settings_page', array( 'PMPro_Hollerbox', 'add_settings' ) );
    	add_filter( 'hollerbox_content', array( 'PMPro_Hollerbox', 'add_content_to_holler' ), 10, 2 );

    	add_action( 'hwp_advanced_settings_after', array( 'PMPro_Hollerbox', 'hwp_advanced_settings_after' ) );
    	add_filter( 'hwp_settings_array', array( 'PMPro_Hollerbox', 'hwp_settings_array' ) );

    	//filter to display the Holler Box according to membership level.
    	add_filter( 'hwp_display_notification', array( 'PMPro_Hollerbox', 'hwp_display_notification' ), 10, 3 );

    }
		/**
         * Add setting for ID. This will filter the content for a hollerbox to display PMPRo sales.
         *
         * @since       0.1
         * @return      void
         */
        public static function add_settings() {

        	if ( !class_exists( 'Holler_Box' ) || !defined( 'PMPRO_VERSION' ) ) {
        		?><h2><?php _e( 'Please ensure all required plugins are installed for PMPro Holler Box Integration.', 'pmpro-hollerbox' ); ?></h2><?php
        		return;
        	}

            if(  isset( $_POST['pmpro_hwp_sale_box'] ) ) {
                update_option( 'pmpro_hwp_sale_box', sanitize_text_field( $_POST['pmpro_hwp_sale_box'] ) );
            }

            ?>
            <h3><?php _e( 'PMPro Signup Notifications', 'pmpro-hollerbox' ); ?></h3>

            <p><?php _e( 'Enter ID of Holler Box to show PMPro signup notifications.', 'pmpro-hollerbox' ); ?></p>
            
            <input id="pmpro_hwp_sale_box" name="pmpro_hwp_sale_box" value="<?php echo esc_html( get_option( 'pmpro_hwp_sale_box' ) ); ?>" type="text" size="10" />
            <p><em><?php _e( 'If there aren\'t any PMPro orders available, the content of the Holler Box will fallback to the default content set in the Holler Box settings.', 'pmpro-hollerbox' ); ?></em></p>
            <?php
        }
    /**
     * Changes content for Holler box if PMPro order content is available.
     *
     * @since 0.1
     * @return string
     */
	public static function add_content_to_holler( $content, $id ){
		
		$setting_id = get_option( 'pmpro_hwp_sale_box' );

		if ( !$setting_id || $id != $setting_id ) {
            return $content;
		}

		$content = PMPro_Hollerbox::getLastOrder( $content );

		return $content;
	}

	/**
	 * Gets latest order from PMPro and returns a string w/ link.
	 *
	 * @since 0.1
	 * @return string
	 */
	public static function getLastOrder( $content ){
		global $wpdb, $pmpro_pages;

		//get latest order from PMPro
		 $sqlQuery = "SELECT user_id, membership_id FROM $wpdb->pmpro_membership_orders ORDER BY timestamp DESC LIMIT 1";
		 $last_pmpro_order = $wpdb->get_results($sqlQuery);

		if ( !empty( $last_pmpro_order ) ) {

			// display/hide avatar image per user.
			$show_avatar = apply_filters( 'pmprohwp_member_avatar', true );

			$user_info = get_userdata( $last_pmpro_order[0]->user_id );
			$avatar = get_avatar( $user_info->user_email, 50 );
			$membership_level = pmpro_getMembershipLevelForUser($last_pmpro_order[0]->user_id);
			$levels_page_title = get_the_title( $pmpro_pages['levels'] );
			$checkout_url = pmpro_url('checkout') . '?level=' . $last_pmpro_order[0]->membership_id;

			//generate the text for hollerbox
			$text = sprintf( __( '%s recently signed up for the %s. %s', 'pmpro-hollerbox' ), ucfirst( $user_info->display_name ), "<a class=\"pmpro_holler_box_link\" href=\"" . $checkout_url . " \">" . $membership_level->name . " membership</a>", "<a class='pmpro_hollerbox_link' href=\"" . pmpro_url("levels") . " \">" . $levels_page_title . "</a>" );

			if ( $show_avatar ) {
				$content = $avatar;	
				$content .= $text;
			} else {
				$content = $text;
			}	
		}
		
		return $content;
	}

	/**
	 * Add our own metakey for post meta.
	 *
	 * @since 0.1
	 * @return array
	 */
	public static function hwp_settings_array( $keys ){

		$pmpro_settings_array = array();

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

		?>
		<hr>
		<?php _e( 'Show this Holler Box for the following PMPro membership level', 'pmpro-hollerbox' ); ?>
		<div class="hwp-settings-group">

			<?php foreach( $levels as $key => $value ) { 
				//get_saved_levels
				$levels_saved = get_post_meta( $post_id, 'pmpro_membership_level_'.$value->id, true );

				?>

                <input type="checkbox" name="pmpro_membership_level_<?php echo $value->id; ?>" value="<?php echo $value->id; ?>" <?php if( !empty( $levels_saved ) ){ ?> checked <?php } ?>><?php echo $value->name ?></input><br>
            <?php } ?>
            </div>
		<?php
	}

	/**
	 * Displays a Holler Box depending on membership level.
	 *
	 * @since 0.1
	 * @return boolean
	 */
	public static function hwp_display_notification( $show_it, $box_id, $post_id ){

		$levels = pmpro_getAllLevels(true, true);

		$membership_level_required = array();

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
		return $show_it;
	}

} // end class


/**
 * The main function responsible for returning the one true PMPro Hollerbox Sales
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


/**
 * The activation hook is called outside of the singleton because WordPress doesn't
 * register the call from within the class, since we are preferring the plugins_loaded
 * hook for compatibility, we also can't reference a function inside the plugin class
 * for the activation function. If you need an activation function, put it here.
 *
 * @since       0.1
 * @return      void
 */
function pmpro_hollerbox_activation() {
    /* Activation functions here */
}
register_activation_hook( __FILE__, 'pmpro_hollerbox_activation' );

function pmpro_hollerbox_deactivation() {
	/* Deactuvation functions here */
}
register_deactivation_hook( __FILE__, 'pmpro_hollerbox_deactivation' );
