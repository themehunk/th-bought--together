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
          
          var arr = [];

          $.each($("input[class='product-checkbox']:checked"), function(){

                  arr.push($(this).attr('data-product-id'));

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