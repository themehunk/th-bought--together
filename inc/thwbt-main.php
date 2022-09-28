<?php 
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'Thwbt_Main' ) ):

    class Thwbt_Main {

    	private static $instance;

        private $type_status = '' ;


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

        add_filter( 'display_post_states', array($this, 'thwbt_display_post_states'), 10, 2 );

        // Enqueue backend scripts
		add_action( 'admin_enqueue_scripts', array( $this, 'thwbt_admin_enqueue_scripts' ) );

		//Enqueue frontend scripts
		add_action( 'wp_enqueue_scripts', array( $this, 'thwbt_frond_enqueue_scripts' ) );
		

		//show 
        add_action( 'woocommerce_after_single_product_summary', array( $this, 'thwbt_show_shortcode' ) );

        //added product to cart
        add_action( 'wp_ajax_thwbt_add_all_to_cart',array( $this, 'thwbt_add_all_to_cart' ) );
		add_action( 'wp_ajax_nopriv_thwbt_add_all_to_cart', array( $this, 'thwbt_add_all_to_cart' ) );

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
    
            <?php 

            $data = get_post_meta($pid, '_thwbt_product_ids', true );

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
                          
                          <p class="form-field thwbt_product_ids_field thwbt-default-check-single">

                          	

                            <input id="thwbt_checked_default_product" name="thwbt_checked_default_product"
                                           type="checkbox" <?php echo esc_attr( get_post_meta( $pid, '_thwbt_checked_default_product', true ) === 'on' ? 'checked' : '' ); ?>/>
                            <label for="thwbt_checked_default_product"><?php esc_html_e( 'Choose Default Single Product', 'th-bought-together' ); ?>	
                          	</label>
                            </p>
                                    
                             
    
           </div>

	   <?php  }


	   public function thwbt_woocommerce_wp_product_select2( $field ) {

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

		            echo '<option type="' . esc_attr($product->get_type() ) . '" value="' . esc_attr( $value ) . '"' . selected( true, true, false ) . '>' . esc_html( wp_strip_all_tags( $product->get_formatted_name() ) ) . '</option>';
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

		    if ( isset( $_POST['thwbt_checked_default_product'] ) ) {

		    $product->update_meta_data( '_thwbt_checked_default_product',$_POST['thwbt_checked_default_product'] );

		    }
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

	    public function thwbt_display_post_states( $states, $post ) {
		
		 $items = get_post_meta($post->ID, '_thwbt_product_ids', true );

 		 if ( ! empty( $items ) ) {

								$count    = count( $items );
								$states[] = esc_html__('TH Bought Together','th-bought-together') . '(' .esc_html($count). ')';

								
			}	

	     return $states;

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

					array_unshift($data_items,$product_id);
		
            ?>

            <section class="thwbt-wrapper">

            	<h2><?php _e('frequently brougt together','th-bought-together');?></h2>

	            <div class="thwbt-content thwbt-product-wrap" data-id="<?php echo esc_attr($product_id);?>" data-thwbt-order="0">
	            	
	            	<div class="thwbt-content-one">

	            	<?php 

	            	$count = 0;

	            	foreach ( $data_items as $item ) {


	                  if($count==0){

                       $thwbt_class ='thwbt-product thwbt-active';

                       

	                  }else{

	                  	$thwbt_class='thwbt-product thwbt-inactive';

	                  	

	                  }

                      $item_product = wc_get_product( $item );

					  if ( ! $item_product || ( ( ! $item_product->is_purchasable() || ! $item_product->is_in_stock() ) ) ) {

							continue;

						} 

						?>

						<div <?php wc_product_class($thwbt_class, $item );?>>

							<div class="image"><?php echo wp_kses_post($item_product->get_image()); ?></div>
							
							<h4>
								<a href="<?php echo esc_url($item_product->get_permalink());?>"><?php echo esc_html($item_product->get_name());?></a>
							</h4>
	            			<?php
	            			echo wp_kses_post($item_product->get_price_html());
	            			?>
	            		
						</div>

					   <?php 

					   $count++;

					   } 

					   ?>
	            		
	            	</div>

	            	<div class="thwbt-content-two thwbt-products">

	            	<div class="thwbt-product-list">

                      <?php foreach ( $data_items as $item ) {

                      $item_product = wc_get_product( $item );

					  if ( ! $item_product || ( ( ! $item_product->is_purchasable() || ! $item_product->is_in_stock() ) ) ) {

							continue;

						} ?>

	            		<div class="thwbt-product-list-add">
	            			
	            			<label>
	            				<input id="<?php echo esc_attr($item_product->get_id());?>" name="product-checkbox[<?php echo esc_attr($item_product->get_id());?>]" value="<?php echo esc_attr($item_product->get_price());?>"type="checkbox" class="product-checkbox" data-name="<?php echo esc_attr($item_product->get_name());?>" data-price="<?php echo esc_attr($item_product->get_price());?>" data-product-id="<?php echo esc_attr($item_product->get_id());?>" data-product-type="<?php echo esc_attr($item_product->get_type());?>"

	            				data-id="<?php echo esc_attr( $item_product->is_type( 'variable' ) || ! $item_product->is_in_stock() ? 0 : $product_id ); ?>"
	            				 
	            				data-product-quantity="1" <?php if($product_id === $item_product->get_id()) echo esc_attr('checked') .esc_attr(' disabled');?>>
								    <span class="thwbt-product-title">
									<?php echo esc_html($item_product->get_name());?>
									</span>
									<span class="thwbt-product-price">
									<?php
	            			        echo wp_kses_post($item_product->get_price_html());?>
	            			        </span>

	            			        <?php if ( $item_product->is_type( 'variable' ) ) {

	            			           $this->thwbt_variable_product($product);

	            			         }

	            			        ?>
							</label>

	            		</div>

	            	   <?php } ?>

	            	   <?php $this->thwbt_total_wrap($product_id);?>

	            		</div>

	            	</div>
	                
	            </div>
            </section>

            <?php 

	    }		

	    public function thwbt_total_wrap($pid){ 
            
            $product = wc_get_product($pid);

            if($product->get_type()!=='variable'){

              $price = $product->get_price();
              $price_html = $product->get_price_html();

            }else{

               $price_html = '';
               $price = '';

            }



	    	?>

	    	<div class="total-price-wrapper" data-total="<?php echo esc_attr($price);?>">

	    		<div class="total-price">
	    			<?php

	    			echo wp_kses_post($price_html);
 
	              ?>	
	            </div>

	    		<div class="total-order"><?php echo sprintf(__('For <span>%s</span> item.','th-bought-together'),'1');?></div>
    
                  <?php $this->thwbt_add_button($product); ?>

	    	</div>


	    <?php }	

	    public function thwbt_add_button($product){ ?> 

                <div class="thwbt-add-button-form">

                <input type="hidden" name="thwbt_ids" class="thwbt-ids thwbt-ids-<?php echo esc_attr( $product->get_id() );?>" data-id="<?php echo esc_attr( $product->get_id() );?>" />

                <input type="hidden" name="quantity" value="1"/>

                <input type="hidden" name="product_id" value="<?php echo esc_attr( $product->get_id() );?>">

                <input type="hidden" name="variation_id" class="variation_id" value="0">

	    		<button type="submit" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() );?>"  class="single_add_to_cart_button button alt thwbt-add-button" <?php if('variable' === $product->get_type()) echo esc_attr(' disabled');?>><?php echo esc_html__('Add all to cart','th-bought-together');?>
	    			
	    		</button>
	    	    </div>

	    <?php }


	    public function thwbt_variable_product($product){

	    	$attributes           = $product->get_variation_attributes();
			$available_variations = $product->get_available_variations();

			if ( is_array( $attributes ) && ( count( $attributes ) > 0 ) ) { ?>

             <div class="variations_form" data-product_id="<?php echo esc_attr(absint( $product->get_id() ));?>" data-product_variations="<?php echo esc_attr(htmlspecialchars( wp_json_encode( $available_variations ) ) );?>" >
             	
             	<div class="variations thwbt-variation">
             		
             	<?php foreach ( $attributes as $attribute_name => $options ) { ?>

                 <div class="variation">

                 <div class="select">
				<?php

			$selected = isset( $_REQUEST[ 'attribute_' . sanitize_title( $attribute_name ) ] ) ? wc_clean( stripslashes( urldecode( $_REQUEST[ 'attribute_' . sanitize_title( $attribute_name ) ] ) ) ) : $product->get_variation_default_attribute( $attribute_name );
		wc_dropdown_variation_attribute_options( array(
									'options'          => $options,
									'attribute'        => $attribute_name,
									'product'          => $product,
									'selected'         => $selected,
									'show_option_none' => sprintf( esc_html__( '%s', 'th-bought-together' ), wc_attribute_label( $attribute_name ) )
									) );

									?>
                        </div>

                  </div>

             	<?php } ?>

             	<div class="reset"><?php echo apply_filters( 'woocommerce_reset_variations_link', '<a class="reset_variations" href="#">'.esc_html__( 'Clear', 'th-bought-together' ).'</a>' ); ?>
             	</div>

             </div>


            </div>



			<?php }


	    }


	    public function thwbt_add_all_to_cart() {


		if ( ! isset( $_POST['product_id'] ) ) {

            return;

        }

		check_ajax_referer( 'thwbt-addto-cart', 'thwbt_nonce' );

		
		if(!empty($_POST['thwbt_ids'])){	

		if ( ! class_exists( 'WC_Form_Handler' ) || empty( $_REQUEST['thwbt_ids'] ) || false === strpos( $_REQUEST['thwbt_ids'], ',' ) ) {
		    return;
		}	

		remove_action( 'wp_loaded', array( 'WC_Form_Handler', 'add_to_cart_action' ), 20 );

        $product_ids = explode( ',', $_REQUEST['thwbt_ids'] );

		$count       = count( $product_ids );

		$number      = 0;

		$quantity       = empty( $_POST['quantity'] ) ? 1 : wc_stock_amount( wp_unslash( $_POST['quantity'] ) );

		$variation_id   = $_POST['variation_id'];

		$variation      = $_POST['variation'];

		

		foreach ( $product_ids as $product_id ) {
		    
		    $product_id        = apply_filters( 'woocommerce_add_to_cart_product_id', absint( $product_id ) );
		    $was_added_to_cart = false;
		    $adding_to_cart    = wc_get_product( $product_id );

		    if ( ! $adding_to_cart ) {
		        continue;
		    }

		    if( $adding_to_cart->get_type()=='simple'){

		    	$variation_id   = '0';

		        $variation      = 'null';


		    }
		    if ( $adding_to_cart && 'variation' === $adding_to_cart->get_type() ) {
						$variation_id = $product_id;
						$product_id   = $adding_to_cart->get_parent_id();

						if ( empty( $variation ) ) {
							$variation = $adding_to_cart->get_variation_attributes();
						}
					}
            
		    $add_to_cart_handler = apply_filters( 'woocommerce_add_to_cart_handler', $adding_to_cart->product_type, $adding_to_cart );

		    $passed_validation = apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $quantity, $variation_id, $variation);

		    if ( $passed_validation && false !== WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variation) ) {

		    	do_action( 'woocommerce_ajax_added_to_cart', $product_id );

		        wc_add_to_cart_message( array( $product_id => $quantity ), true );
		       
		        }

		    }

		}else{


		$product_id     = (int) apply_filters( 'woocommerce_add_to_cart_product_id', absint( $_POST['product_id'] ) );

        $passed_validation = apply_filters('woocommerce_add_to_cart_validation', true, $product_id, $variation_id, $variation);

        $product        = wc_get_product( $product_id );

        $quantity       = empty( $_POST['quantity'] ) ? 1 : wc_stock_amount( wp_unslash( $_POST['quantity'] ) );

        $variation_id   = $_POST['variation_id'];
		$variation      = $_POST['variation'];

		if( $product->get_type()=='simple'){

		    	$variation_id   = '0';

		        $variation      = 'null';


		    }
		if ( $product && 'variation' === $product->get_type() ) {
						$variation_id = $product_id;
						$product_id   = $product->get_parent_id();

						if ( empty( $variation ) ) {

							$variation = $product->get_variation_attributes();
						}
		}

        if($passed_validation && WC()->cart->add_to_cart($product_id, $quantity, $variation_id, $variation)){

          $data = apply_filters('add_to_cart_fragments', array());

          do_action( 'woocommerce_ajax_added_to_cart', $product_id );

          if ( 'yes' === get_option( 'woocommerce_cart_redirect_after_add' ) ) {

            wc_add_to_cart_message( array( $product_id => $quantity ), true );

                 }


            }

		}

		WC_AJAX::get_refreshed_fragments();

		die();			

	    
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

	    	wp_enqueue_script( 'thwbt-frontend-js', THWBT_PLUGIN_URI . 'assets/js/frontend.js', array(
						'jquery'), THWBT_VERSION, true );

	    	wp_localize_script( 'thwbt-frontend-js', 'thwbt_optn', 
	    		            array(
							      'ajax_url'                 => admin_url( 'admin-ajax.php' ),
							      'currency_symbol'          => get_woocommerce_currency_symbol(),
							      'nonce'          => wp_create_nonce( "thwbt-addto-cart" ),
						          )
	                           );

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