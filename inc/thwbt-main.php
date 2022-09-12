<?php 
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'Thwbt_Main' ) ):

    class Thwbt_Main {

    	private static $instance;

    	/**
         * Initiator
         */
        public static function instance() {
            if ( ! isset( self::$instance ) ) {
                self::$instance = new self();
            }
            return self::$instance;
        }
        /**
         * Constructor
         */
        public function __construct(){
        
        add_filter( 'woocommerce_product_data_tabs', array( $this,'thwbt_new_product_tab' ), 10, 1);
        add_action( 'woocommerce_product_data_panels',array( $this, 'thwbt_product_tab_content' ) );

        add_action( 'woocommerce_admin_process_product_object',array( $this, 'thwbt_save_admin_process_product_object'), 10, 1 );

        add_shortcode('thwbt', array( $this, 'thwbt_shortcode' ) );

        // Enqueue backend scripts
		add_action( 'admin_enqueue_scripts', array( $this, 'thwbt_admin_enqueue_scripts' ) );

		//Enqueue frontend scripts
		add_action( 'wp_enqueue_scripts', array( $this, 'thwbt_frond_enqueue_scripts' ) );
		

		//show 
        add_action( 'woocommerce_after_single_product_summary', array( $this, 'thwbt_show_shortcode' ) );
        }

        public function thwbt_new_product_tab( $tabs ) {
	
			$tabs['thwbt'] = array(
						'label'  => esc_html__( 'TH Bought Together', 'th-bought-together' ),
						'target' => 'thwbt_tab_settings',
					);

					return $tabs;
		}


		public function thwbt_product_tab_content() {

					global $post;
					$pid = $post->ID;
					$this->thwbt_choose_product($pid);
					
					?>


	    <?php }

	   
	    public function thwbt_choose_product($pid){ ?>

	    	<div id='thwbt_tab_settings' class='panel woocommerce_options_panel thwbt_option'>
    
           <?php $data = get_post_meta($pid, '_thwbt_product_ids', true );

		    // Add field via custom function

		    $this->thwbt_woocommerce_wp_product_select2(
		        array(
		            'id'            => 'thwbt_product_ids',
		            'label'         => __( 'Choose Product', 'th-bought-together' ),
		            'placeholder'   => __( 'Add Product', 'th-bought-together' ),
		            'class'         => '',
		            'name'          => 'thwbt_product_ids[]',
		            'value'         =>  $data,
		            'desc_tip'      => true,
		            'description'   => __( 'Choose Product', 'th-bought-together' ),
		        )
		    );

		    ?>
    
           </div>

	   <?php  }


	   public function thwbt_woocommerce_wp_product_select2( $field ) {

		    global $post;

		    $field['placeholder']   = isset( $field['placeholder'] ) ? $field['placeholder'] : '';

		    $field['class']         = isset( $field['class'] ) ? $field['class'] : 'select short';

		    $field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : 'thwbt-product-select';

		    $field['value']         = ! empty( $field['value'] ) ? $field['value'] : array();

		    $field['name']          = isset( $field['name'] ) ? $field['name'] : $field['id'];

		    echo '<p class="form-field ' . esc_attr( $field['id'] ) . '_field ' . esc_attr( $field['wrapper_class'] ) . '">

		        <label for="' . esc_attr( $field['id'] ) . '">' . wp_kses_post( $field['label'] ) . '</label>

		        <select id="' . esc_attr( $field['id'] ) . '" name="' . esc_attr( $field['name'] ) . '" class="wc-product-search ' . esc_attr( $field['class'] ) . '" multiple="multiple" style="width: 50%;" data-maximum-selection-length="5" data-placeholder="' . esc_attr( $field['placeholder'] ) . '"  >';

		    foreach ( $field['value'] as $key => $value ) {
		        $product = wc_get_product( $value );
		        if ( is_object( $product ) ) {
		            echo '<option value="' . esc_attr( $value ) . '"' . selected( true, true, false ) . '>' . esc_html( wp_strip_all_tags( $product->get_formatted_name() ) ) . '</option>';
		        }
		    }

		    echo '</select> ';

		    if ( ! empty( $field['description'] ) ) {
		        if ( isset( $field['desc_tip'] ) && false !== $field['desc_tip'] ) {
		            echo '<span class="woocommerce-help-tip" data-tip="' . esc_attr( $field['description'] ) . '"></span>';
		        } else {
		          echo '<span class="description">' . wp_kses_post( $field['description'] ) . '</span>';
		        }
		    }

		    echo '</p>';
		}


		// Save

		public function thwbt_save_admin_process_product_object( $product ) {

		    $data = isset( $_POST['thwbt_product_ids'] ) ? (array) $_POST['thwbt_product_ids'] : array();

		    // Update
		    $product->update_meta_data( '_thwbt_product_ids', array_map( 'esc_attr', $data ) );
		}

        //create shortcode

		public function thwbt_shortcode( $attrs ) {

					$attrs = shortcode_atts( array( 'id' => null, 'location' => true ), $attrs );

					ob_start();

					self::thwbt_show_items( $attrs['id'], $attrs['location'] );

					return ob_get_clean();

				}

	    public function thwbt_show_shortcode(){

            echo do_shortcode('[thwbt]');

	    }

	    public function thwbt_show_items($product_id = null, $location = false){

	    	if ( ! $product_id ) {

				global $product;

					
					if ( $product ) {

							$product_id = $product->get_id();
						}

					} else {

						$product = wc_get_product( $product_id );
					}

					if ( ! $product_id || ! $product ) {

						return;
					}

					$data_items = get_post_meta($product_id, '_thwbt_product_ids', true );

					if(empty($data_items)){
                        
                        return;
					}

					
            ?>

            <section class="thwbt-wrapper">

            	<h2><?php _e('frequently brougt together','th-bought-together');?></h2>

	            <div class="thwbt-content">
	            	
	            	<div class="thwbt-content-one">

	            	<?php foreach ( $data_items as $item ) {

                      $item_product = wc_get_product( $item );

					  if ( ! $item_product || ( ( ! $item_product->is_purchasable() || ! $item_product->is_in_stock() ) ) ) {

							continue;

						} ?>

						<div <?php wc_product_class( 'thwbt-product', $item );?>>

							<div class="image"><?php echo $item_product->get_image(); ?></div>
							
							<h4><a href="<?php echo esc_url($item_product->get_permalink());?>"><?php echo $item_product->get_name();?></a></h4>
	            			<?php
	            			echo $item_product->get_price_html();
	            			?>
	            		
						</div>

					   <?php } ?>
	            		
	            	</div>

	            	<div class="thwbt-content-two">
	            		left
	            	</div>
	                
	            </div>
            </section>

            <?php 

	    }			


	    public function thwbt_admin_enqueue_scripts(){

	    	wp_enqueue_style( 'thwbt-backend-css', THWBT_PLUGIN_URI . 'assets/css/backend.css', array(), THWBT_VERSION );

	    	wp_enqueue_script( 'thwbt-backend-js', THWBT_PLUGIN_URI . 'assets/js/backend.js', array(
						'jquery',
						'jquery-ui-dialog',
						'jquery-ui-sortable'
					), THWBT_VERSION, true );

	    }

	    public function thwbt_frond_enqueue_scripts(){

	    	wp_enqueue_style( 'thwbt-frontend-css', THWBT_PLUGIN_URI . 'assets/css/frontend.css', array(), THWBT_VERSION );

	    	

	    }


    }

/*****************/    
// Load Plugin
/*****************/ 
function thwbt_main_load(){

        return Thwbt_Main::instance();

}
add_action( 'plugins_loaded', 'thwbt_main_load', 25 );

endif;    

 