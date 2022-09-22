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

				$products.find('.thwbt-product-list-add').each(function() {

				var $this = $(this);

				var _checked = $this.find('.product-checkbox').prop('checked');

				var _id = parseInt($this.find('.product-checkbox').attr('data-product-id'));
        
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

            var table_abc = document.getElementsByClassName("product-checkbox");

            for (var i = 0; table_abc[i]; ++i) {

		        if (table_abc[i].checked) {

		            var value = table_abc[i].value;

		            _total += parseFloat(table_abc[i].value);

		        }
		    }

		    $('.total-price').html(_total); 

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