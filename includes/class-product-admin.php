<?php
/**
 * Class which manages all adjustments in admin side for our custom product type.
 *
 * @package woo-everwebinar;
 */

namespace Woo_EverWebinar;

/**
 * Class Product_Admin
 *
 * @package woo-everwebinar;
 */
class Product_Admin {

	/**
	 * Custom Product type slug.
	 *
	 * @var string
	 */
	public $product_type = ProductType;

	/**
	 * Label of custom product type.
	 *
	 * @var string
	 */
	public $product_label = ProductTypeLabel;

	/**
	 * Name of meta saved for Product CPT.
	 *
	 * @var string
	 */
	private $product_webinar_id_meta = 'everwebinar_id';

	/**
	 * Product_Admin constructor.
	 */
	public function __construct() {

		$this->settings = new Settings();

		// init actions here.
		add_filter( 'product_type_selector', array( $this, 'add_type' ) );
		add_filter( 'woocommerce_product_class', array( $this, 'map_product_classname' ), 10, 4 );
		add_filter( 'woocommerce_product_data_tabs', array( $this, 'add_product_tab' ) );
		add_action( 'woocommerce_product_data_panels', array( $this, 'product_tab_content' ) );

		// saving of product's meta - attaching webinar id.
		add_action( 'woocommerce_process_product_meta_' . $this->product_type, array( $this, 'save_option_field' ) );
		add_action( 'admin_footer', array( $this, 'product_type_selector_js' ) );

		add_action( 'woocommerce_' . $this->product_type . '_add_to_cart', array( $this, 'add_to_cart_button' ) );

		add_action( 'add_meta_boxes', array( $this, 'add_order_metabox' ) );

		add_filter( 'woocommerce_email_classes', array( $this, 'custom_emails' ) );

	}

	/**
	 * Adds Product type.
	 *
	 * @param array $types
	 * @return array
	 */
	public function add_type( $types = array() ) {

		// Key should be exactly the same as in the class product_type parameter.
		$types[ $this->product_type ] = $this->product_label;

		return $types;
	}

	/**
	 * Sets right ClassName for our custom Product Type, used in \WC_Product_Factory.
	 *
	 * @param $product_class_name
	 * @param $product_type
	 * @param $product_variation
	 * @param $product_id
	 * @return string
	 */
	public function map_product_classname( $product_class_name, $product_type, $product_variation, $product_id ) {

		if ( $product_type === $this->product_type ) {
			return '\Woo_EverWebinar\WC_Product_Everwebinar';
		}

		return $product_class_name;
	}

	/**
	 * Adjust product's tab
	 *
	 * @param array $tabs Array of tabs for product type.
	 * @return array
	 */
	public function add_product_tab( $tabs = array() ) {

		$hide_if_class = ' hide_if_' . $this->product_type . ' ';
		$show_if_class = ' show_if_' . $this->product_type . ' ';

		// first hide unneeded ones.
		// Other default values for 'attribute' are; general, inventory, shipping, linked_product, variations, advanced.
		$tabs['attribute']['class'][]  = $hide_if_class;
		$tabs['inventory']['class'][]  = $hide_if_class;
		$tabs['shipping']['class'][]   = $hide_if_class;
		$tabs['variations']['class'][] = $hide_if_class;
		$tabs['advanced']['class'][]   = $hide_if_class;

		// create our own tab.
		$mytab = array(
			$this->product_type => array(
				'label'  => __( 'Select Webinar', 'woo-everwebinar' ),
				'target' => 'everwebinar_options',
				'class'  => array( $show_if_class, 'hide_if_simple', 'hide_if_variable', 'hide_if_grouped', 'hide_if_external' ),
			),
		);

		return $mytab + $tabs;
	}

	/**
	 * Contents of everwebinar select webinar product tab.
	 */
	public function product_tab_content() {

		// lets get webinar list and select one in dropdown! // simplest) .
		$everwebinar_api_key = get_option( 'everwebinar_api_key', '' );
		$webinarlist         = list_webinars( $everwebinar_api_key );
		$webinars            = array();
		if ( is_array( $webinarlist ) && ! is_wp_error( $webinarlist ) ) {
			foreach ( $webinarlist as $webinar ) {
				$webinars[ $webinar->webinar_id ] = $webinar->name;
			}
		}

		?>
		<div id='<?php echo esc_attr( $this->product_type ); ?>_options' class='panel woocommerce_options_panel'>
			<div class='options_group show_if_<?php echo esc_attr( $this->product_type ); ?>'>
				<?php
				if ( empty( $everwebinar_api_key ) ) {
					?>
					<h2>You need to specify EverWebinar API key first</h2>
					<p><a href="/wp-admin/admin.php?page=wc-settings&tab=products&section=woo_everwebinar">click here to set API key</a> then go to EverWebinar and create Webinars to sell</p>
					<p>then select here in dropdown list needed webinar to sell.</p>
					<?php
				} elseif ( is_wp_error( $webinarlist ) ) {
					?>
					<h2>Error loading webinars</h2>
					<p>Possible wrong API key</p>
					<p><p><a href="/wp-admin/admin.php?page=wc-settings&tab=products&section=woo_everwebinar">click here to set API key</a></p>
					<?php
				} elseif ( count( $webinars ) < 1 ) {
					?>
					<h2>No webinars loaded from EverWebinar</h2>
					<p>Create new webinar on EverWebinar admin panel and try again.</p>
					<?php
				} else {
					woocommerce_wp_select(
						array(
							'id'          => $this->product_webinar_id_meta,
							'name'        => $this->product_webinar_id_meta,
							'label'       => __( 'Webinar to sell' ),
							'desc_tip'    => 'true',
							'description' => __( 'Select Webinar to sell, they are sorted by creation date, latest first', 'woo-everwebinar' ),
							'options'     => $webinars,
						)
					);
				}

				?>
			</div>
		</div>
		<?php

	}

	/**
	 * Save the custom fields for product.
	 *
	 * @param int $post_id  Product's post_id we work with.
	 */
	public function save_option_field( $post_id ) {

		// check_admin_referer( 'editpost' );

		if ( isset( $_POST[ $this->product_webinar_id_meta ] ) ) {
			update_post_meta(
				$post_id,
				$this->product_webinar_id_meta,
				sanitize_text_field( wp_unslash( $_POST[ $this->product_webinar_id_meta ] ?? '' ) )
			);
		}
		update_post_meta( $post_id, '_virtual', 'yes' );
		update_post_meta( $post_id, '_sold_individually', 'yes' );
		update_post_meta( $post_id, '_manage_stock', 'no' );
		update_post_meta( $post_id, '_backorders', 'no' );
	}

	/**
	 * Sets our custom switch class for product type in admin UI.
	 */
	public function product_type_selector_js() {
		if ( 'product' !== get_post_type() ) :
			return;
		endif;
		?>
		<script type='text/javascript' id="<?php echo esc_attr( $this->product_type ); ?>-product-pricing-tab-enabler">
			jQuery( '.options_group.pricing' ).addClass( 'show_if_<?php echo esc_attr( $this->product_type ); ?>' );
		</script>
		<?php
	}

	/**
	 * Crazy hook to show button to buy item.
	 */
	public function add_to_cart_button() {
		wc_get_template( 'single-product/add-to-cart/simple.php' );
	}

	/**
	 * Adds metabox to Order admin screen.
	 */
	public function add_order_metabox() {

		if ( order_has_webinars() ) {

			add_meta_box(
				'woocommerce-order__' . $this->product_type,
				__( 'EverWebinar registration results', 'woo-everwebinar' ),
				array( $this, 'render_order_metabox_content' ),
				'shop_order',
				'side',
				'default'
			);
		}
	}

	/**
	 * Order Metabox with registration results renderer.
	 */
	public function render_order_metabox_content() {

		$reg_results = get_webinar_registration_results_from_order();

		if ( is_array( $reg_results ) ) {
			echo '<style> 
            .everwb_wrapper{ position: relative; margin: 0 -12px; counter-reset: everwebinarcount;}  
            .everwb_webinar_item{position: relative; padding: 12px; } 
            .everwb_webinar_item:before{
                counter-increment: everwebinarcount;
                content: counter(everwebinarcount) " Webinar in order"; 
                display: block; 
                text-align: center; 
                font-weight: 700; 
                padding-bottom: 1em;
            }
            .everwb_webinar_item:first-child:last-child:before{
                display: none;
            } 
            .everwb_webinar_item + .everwb_webinar_item{ padding-top: 3em;}  
            .everwb_webinar_item:nth-child(odd){background: white;} 
            .everwb_webinar_item:nth-child(even){background: rgba(0, 115, 170, 0.1);} 
            .everwb_row{display: block;}  
            .everwb_row label{display: block; font-weight: bolder;} 
            .everwb_row input{width: 100%;}  
            </style>';

			echo '<div class="everwb_wrapper">';
			foreach ( $reg_results as $reg_result ) {
				echo '<div class="everwb_webinar_item">';
				foreach ( $reg_result as $name => $value ) {
					echo '
				     <div class="everwb_row">
				        <label for="reg_result_' . $name . '">' . $name . '</label>
				        <input id="reg_result_' . $name . '" type="text"  value="' . $value . '">
				     </div>';
				}
				echo '</div>';
			}
			echo '</div>';
		}
	}

	/**
	 * Links our Emails/
	 *
	 * @param array $email_classes
	 * @return array
	 */
	public function custom_emails( $email_classes = array() ) {

		$email_classes['Woo_EverWebinar_Registered_Email']       = new Emails\Woo_EverWebinar_Registered_Email();
		$email_classes['Woo_EverWebinar_Registered_Email_Admin'] = new Emails\Woo_EverWebinar_Registered_Email_Admin();
		$email_classes['Woo_EverWebinar_Error_Email']            = new Emails\Woo_EverWebinar_Error_Email();

		return $email_classes;
	}
}
