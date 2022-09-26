/**
 * Frondend Js
 */

(function($){

	'use strict';

	var ThwbtScript;

	ThwbtScript = {

		init: function()
		{
			this._bind();
		},

		/**
		 * Binds events 
		 *
		 * @since 1.0.0
		 * @access private
		 * @method _bind
		 */

		_bind: function()
		{

         $( document ).on('click' , '.product-checkbox', ThwbtScript._thwbt_init );
         $( document ).on('click' , '.thwbt-add-button-form .single_add_to_cart_button', ThwbtScript._add_to_cart_item );
			
		},

       /*************/
      // Main Init
      /*************/
		_thwbt_init : function( event ) {

            $('.thwbt-product-wrap').each(function() {	

            ThwbtScript._checkvalue_used($(this));

            ThwbtScript._calculate_total_price($(this));

            });

		},

	  /************************/
      // checkbox value save it
      /************************/

		_checkvalue_used : function( $wrap ) {

			  $wrap.each(function() {

				var $products = $(this).find('.thwbt-products');

				var $btn = $(this).find('.single_add_to_cart_button.thwbt-add-button');

				var is_selection = false;

				$products.find('.thwbt-product-list-add').each(function() {

				var $this = $(this);

				var _checked = $this.find('.product-checkbox').prop('checked');

				var _id = parseInt($this.find('.product-checkbox').attr('data-product-id'));

				var _prd_type = $this.find('.product-checkbox').attr('data-product-type');

				var _prd_name = $this.find('.product-checkbox').attr('data-name');
        
        var $match_id = $this.closest($wrap).find('.thwbt-content-one');
			      

			    if (!_checked) {

			      if ($match_id.length) {
			        $match_id.find('.post-' + _id).
			            addClass('thwbt-inactive');
			      }

			    } else {

			      if ($match_id.length) {
			        $match_id.find('.post-' + _id).
			            removeClass('thwbt-inactive');
			      }

			    }


			    if (_checked && (_prd_type == 'variable')) {
              
              is_selection = true;

			    }

			    if (is_selection) {

			    	$btn.attr('disabled', 'disabled');

			    }else{

			    	$btn.removeAttr("disabled");

			    }


			}); 




     });


		},

       /************************/
      // Calculate Total Price
      /************************/

		_calculate_total_price : function( $wrap ) {

         var $products     = $wrap.find('.thwbt-product-list');

         var $product_this = $products.find('.product-checkbox');


         $products.find('.thwbt-product-list-add').each(function() {

          	var $this = $(this);
            var _total = 0;
            var _count = 0;
            var _id = [];


            var table_abc = document.getElementsByClassName("product-checkbox");

            for (var i = 0; table_abc[i]; ++i) {

		        if (table_abc[i].checked) {

		            _total += parseFloat(table_abc[i].value);

		             _id.push(parseInt(table_abc[i].id));

		            _count++;

		        }
		    }

		    $('.total-price').html(thwbt_optn.currency_symbol + _total);

		    $('.total-order span').html(_count);

		    $(".thwbt-ids").attr("value",_id);

        });


		},
        
       /************************/
      // Add to cart Item
      /************************/

       _add_to_cart_item : function( event ) {
          
          event.preventDefault();

          var $btn = $(this);

          var $form = $btn.closest('.thwbt-add-button-form');
          var $wrap = $btn.closest('.thwbt-product-wrap');

         // variable product

         var data = {};
         var attrs = {};

         $btn.addClass('loading');

         data.action = 'thwbt_add_all_to_cart';

         data.quantity = $form.find('input[name="quantity"]').val();

         data.product_id = $form.find('input[name="product_id"]').val();

         data.thwbt_ids = $form.find('input[name="thwbt_ids"]').val();

         data.thwbt_nonce = thwbt_optn.nonce;

         $.post(thwbt_optn.ajax_url, data, function(response) {


          if (!response) {
            return;
          }

          if (response.error && response.product_url) {
            window.location = response.product_url;
            return;
          }

          if ((typeof wc_add_to_cart_params !== 'undefined') &&
              (wc_add_to_cart_params.cart_redirect_after_add === 'yes')) {
            window.location = wc_add_to_cart_params.cart_url;
            return;
          }

          $btn.removeClass('loading');

          $(document.body).
              trigger('added_to_cart',
                  [response.fragments, response.cart_hash, $btn]);
        });
             
       }

	};

	/**
	 * Initialize ThwbtScript
	 */

	$(function(){

		ThwbtScript.init();

	});

})(jQuery);