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

    $( document ).on('click' , '.product-checkbox', ThwbtScript._checkvalue_used );
			
		},

		/*************/
    // checkbox value save it
     /*************/

		_checkvalue_used : function( event ) {

			$('.thwbt-product-wrap').each(function() {

				var $products = $(this).find('.thwbt-products');

				$products.find('.thwbt-product-list-add').each(function() {

				var $this = $(this);

				var _checked = $this.find('.product-checkbox').prop('checked');

				var _id = parseInt($this.find('.product-checkbox').attr('data-product-id'));
        
        var $match_id = $this.closest('.thwbt-product-wrap').find('.thwbt-content-one');
			        
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


		}

		
	};

	/**
	 * Initialize ThwbtScript
	 */

	$(function(){

		ThwbtScript.init();

	});

})(jQuery);