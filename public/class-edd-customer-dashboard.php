<?php
/**
 * EDD Customer Dashboard
 *
 * @package   EDD_Customer_Dashboard
 * @author    Josh Mallard <josh@limecuda.com>
 * @license   GPL-2.0+
 * @link      http://joshmallard.com
 */

/**
 * EDD Customer Dashboard Class
 *
 * @package EDD_Customer_Dashboard
 * @author  Josh Mallard <josh@limecuda.com>
 */
class EDD_Customer_Dashboard {

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since   1.0.0
	 *
	 * @var     string
	 */
	const VERSION = '1.0.0';

	/**
	 * text-domain variable
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_slug = 'edd_customer_dashboard';

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {


		require_once( plugin_dir_path( __FILE__ ). '../includes/process-avatar.php' );

		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );


		// Load public-facing style sheet and JavaScript.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );

		add_shortcode( 'edd_customer_dashboard', array( $this, 'edd_customer_dashboard_shortcode' ) );

	}

	/**
	 * Return the plugin slug.
	 *
	 * @since    1.0.0
	 *
	 * @return    Plugin slug variable.
	 */
	public function get_plugin_slug() {
		return $this->plugin_slug;
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		$domain = $this->plugin_slug;
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, FALSE, basename( plugin_dir_path( dirname( __FILE__ ) ) ) . '/languages/' );

	}

	/**
	 * Register and enqueue public-facing style sheet.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_slug . '-plugin-styles', plugins_url( 'style.css', __FILE__ ), array(), self::VERSION );
	}

	/**
	 * Build the dashboard shortcode
	 *
	 * @since 1.0.0
	 */
	public function edd_customer_dashboard_shortcode( $atts, $content = "" ) {

		ob_start();

		if( is_user_logged_in() ) {

			$menu_items = $this->build_menu();
			$dashboard_content = $this->build_page_content();

			// Customer dashboard menu
			do_action( 'edd_cd_before_menu' );
			include_once( plugin_dir_path( __FILE__ ) . '/templates/customer-dashboard-menu.php' );
			do_action( 'edd_cd_after_menu' );

			?>
			<div class="customer-dashboard-content">
				<?php
				do_action( 'edd_cd_before_content' );

				echo $dashboard_content;

				do_action( 'edd_cd_after_content' );
				?>
			</div>

			<?php
		} else {
			echo do_shortcode( '[edd_login redirect="'.the_permalink().'"]' );
		}

		$dashboard = ob_get_clean();

		return $dashboard;

	}

	/**
	 * Customer dashboard menu items
	 *
	 * @since    1.0.0
	 * @return   array
	 */
	public function build_menu() {
		$menu = array();

		$menu[ 'profile' ] = array(
			'task' => 'profile',
			'name' => __( 'Profile', 'edd_customer_dashboard' )
		);

		$menu[ 'purchases' ] = array(
			'task' => 'purchases',
			'name' => __( 'Purchase History', 'edd_customer_dashboard' )
		);

		$menu[ 'downloads' ] = array(
			'task' => 'download',
			'name' => __( 'Downloads', 'edd_customer_dashboard' )
		);

		// Support for EDD Wishlists
		if( class_exists( 'EDD_Wish_Lists' ) ) {
			$menu[ 'wishlists' ] = array(
				'task'=> 'wishlist',
				'name' => __( 'Wishlists', 'edd_customer_dashboard' )
			);
		}

		$menu = apply_filters( "edd_customer_dashboard_menu_links", $menu );
		return $menu;

	}

	/**
	 * Customer dashboard page content
	 *
	 * @since	1.0.0
	 */
	public function build_page_content() {
		$task = !empty( $_GET[ 'task' ] ) ? $_GET[ 'task' ] : '';

		ob_start();

		$custom = apply_filters('edd_cd_custom_task', false, $task );
		switch ( $task ) {

			case 'profile' :
				echo '<h2>' . __( 'Profile','edd_customer_dashboard') . '</h2>';
				//$this->custom_avatar();
				echo do_shortcode( '[edd_profile_editor]' );
			break;

			case 'purchases' :
				echo '<h2>' . __( 'Purchase History', 'edd_customer_dashboard' ) . '</h2>';
				echo do_shortcode( '[purchase_history]' );
			break;

			case 'download' :
				echo '<h2>' . __( 'Download History', 'edd_customer_dashboard' ) . '</h2>';
				echo do_shortcode( '[download_history]' );
			break;

			case 'wishlist' :
				echo '<h2>' . __( 'Wishlists', 'edd_customer_dashboard' ) . '</h2>';
				echo do_shortcode( '[edd_wish_lists]' );
			break;

			case 'fes_become_vendor' :
				echo '<h2>' . __( 'Become a Vendor', 'edd_customer_dashboard' ) . '</h2>';
				echo do_shortcode( '[fes_registration_form]' );
			break;

			case $custom :
				do_action( 'edd_cd_custom_task_content' );
			break;

			default :

			break;

		}

		$dashboard_content = ob_get_clean();

		return $dashboard_content;

	}

	public function custom_avatar(){

		?>
			<form class="edd_form" action="#profile-information" method="POST" id="profile-form">
				<fieldset>
					<span id="edd_profile_name_label"><legend>Change your Avatar</legend></span>
						<p class="profile-image-upload">
							<label for="profile_image" class="profile-label">
								<?php
								$avatar = get_user_meta(get_current_user_id(), 'profile_avatar_image', true);

								if($avatar) {
									echo '<img src="' . $avatar . '"/>';
								}

								?>
								<span class="profile-image-upload-note"><strong>Custom Avatar</strong> - For best results, use a square image sized at least 150px x 150px.</span>
							</label>
							<input type="file" name="profile_avatar_img" id="profile_avatar_img"/>
						</p>
						<p id="profile-submit" class="clear">
							<input type="hidden" name="user_id" value="<?php echo get_current_user_id(); ?>"/>
							<input type="hidden" name="action" value="update_profile"/>
							<input type="hidden" name="profile_nonce" value="<?php echo wp_create_nonce('profile-nonce'); ?>"/>
							<input type="submit" name="save_profile" value="Save Avatar"/>
						</p>
				</fieldset>

			</form>
		<?php

	}

}
