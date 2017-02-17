(function($){

	$(document).ready(function(){
		var featured_image = $('#postimagediv .inside'),
			project_color = $('#_project_color');

		if ( project_color.length > 0 ) {

			featured_image.on('html-change-post', function() {
				var image = featured_image.find('#set-post-thumbnail img');
				if ( image.length > 0 ) {

					var alt = $(image).attr('alt'),
						src = $(image).attr('src');

					$.ajax({
						type: "post",
						url: ajaxurl,
						data: { action: 'pxg_get_project_color', attachment_src: src },
						success:function(response){

							if ( typeof response.success !== "undefined" && ! response.success ) return;

							var color = '#' + response.data,
								isColor  = /(^#[0-9A-F]{6}$)|(^#[0-9A-F]{3}$)/i.test(color);

							if ( isColor ) {
								//$('#postimagediv').attr('style', 'background-color:'+ color);

								var palettes = get_colorpicker_palettes(color);

								// setup the color and the new palettes
								$('#_project_color')
									.iris('option', 'palettes', palettes )
									.val(color)
									.trigger('change');
							}
						}
					});
				}
			});

			var get_colorpicker_palettes = function( color ){

				var palettes = [],
					darker1 = ColorLuminance(color, -0.5),
					darker2 = ColorLuminance(color, -0.25),
					lighter1 = ColorLuminance(color, 0.25),
					lighter2 = ColorLuminance(color, 0.5),
					current_color = $('#_project_color').val();


				// in the future save the old color in palette
				//if(typeof(Storage) !== "undefined") {
				//
				//}

				palettes = ['#fff', lighter2, lighter1, color, darker2, darker1, '#000', current_color];

				return palettes;
			};

			$(document).on('mouseup', '#_project_aside .iris-square-inner, #_project_aside .iris-slider-offset, #_project_aside .iris-palette-container', function(){

				var el = $('<input type="hidden" id="_project_color_forced_by_user" name="_project_color_forced_by_user" value="'+ curent_colorpicker.val() +'" />');

				if ( $('#_project_color_forced_by_user').length  == 0 ) {
					project_color.parent().append( el );
				} else {
					$('#_project_color_forced_by_user').val(project_color.val());
				}

			});
		}
	});

	$(window).load(function () {

		/**
		 * check the number of slides for this page.It checks the image gallery, video playlist and the number of
		 * featured projects if they are visible
		 * @returns {*}
		 */
		var check_number_of_slides = function() {
			var featured = 0,
				images = 0,
				videos = 0;

			if ( $('#_portfolio_featured_projects').parents('.cmb-type').is(':visible') && '' !== $('#_portfolio_featured_projects').val() ) {
				featured = $('#_portfolio_featured_projects').val().split(',').length;
			}

			if ( typeof $('#pixgalleries').val() !== "undefined" && '' !== $('#pixgalleries').val() ) {
				images =  $('#pixgalleries').val().split(',').length;
			}

			if (  typeof $('#pixplaylist').val() !== "undefined" && '' !== $('#pixplaylist').val() ) {
				videos =  $('#pixplaylist').val().split(',').length;
			}

			return ( images + videos + featured );
		};

		var has_description_content = function() {
			if( typeof (tinyMCE) === "undefined" ) {
				return !!$('#_hero_content_description').val();
			}

			var hero_editor = tinyMCE.get('_hero_content_description');

			if ( typeof hero_editor === "undefined" || hero_editor === null ) {
				return false;
			}
			return hero_editor.getContent().length;
		};

		var check_hero_desc_visibility = function () {

			$('#wp-_hero_content_description-wrap').siblings('.cmb_metabox_description').addClass('hero-visibility');
			if ( ! has_description_content() && check_number_of_slides() < 1  ) {
				$('#hero_area_content__page').addClass('is--hidden').removeClass('is--visible');
			} else {
				$('#hero_area_content__page').addClass('is--visible').removeClass('is--hidden');
			}
		};

		/**
		 * Here we check if we need to display the slider settings or not
		 */
		var toggleSlidesOptionsDisplay = function () {
			check_hero_desc_visibility();

			$('#_hero_slideshow_options__title').parents('.cmb-type.cmb-type-title').addClass('slideshow-area-title');

			if ( check_number_of_slides() > 1 ) {
				$('.slideshow-area-title').addClass('is--enabled').removeClass('is--disabled');

				$('#_hero_slideshow_options__autoplay, #_hero_slideshow_options__delay').each(function () {
					$(this).parents('.cmb-type').removeClass('has--no-slides');
				})
			} else {
				$('.slideshow-area-title').addClass('is--disabled').removeClass('is--enabled');

				$('#_hero_slideshow_options__autoplay, #_hero_slideshow_options__delay').each(function () {
					$(this).parents('.cmb-type').addClass('has--no-slides');
				})
			}
		};

		setTimeout(toggleSlidesOptionsDisplay, 300 );

		// classify the gallery number
		$('#pixgallery, #pixvideos').on( 'html-change-post', function() {
			toggleSlidesOptionsDisplay();
		});

		$('#_portfolio_featured_projects').on('change', function () {
			toggleSlidesOptionsDisplay();
		});


		if( typeof (tinyMCE) === "undefined" ) return;


		$('<span class="hero-hidden-overlay  dashicons  dashicons-hidden"></span>').insertAfter('#_hero_content_description_ifr');

		check_hero_desc_visibility();

		var hero_editor = tinyMCE.get('_hero_content_description');

		if ( typeof hero_editor !== "undefined" && hero_editor !== null  ) {
			hero_editor.on('keyup', function  (e) {
				check_hero_desc_visibility();
			});
		}

		var $hero_color =  $('#_hero_background_color');

		if ( $hero_color.length > 0 ) {
			hero_add_editor_bg_color( $hero_color.val() );

			$hero_color.on('wpcolorpicker:change', function ( ev ) {
				var color = $(this).val();
				hero_add_editor_bg_color(color);
			} );
		}
	});

	var hero_add_editor_bg_color = function( color ) {
		var $hero_desc_ifr = $('#wp-_hero_content_description-wrap').find('iframe');

		if ( $hero_desc_ifr.length > 0 ) {
			$hero_desc_ifr.contents().find('body').css({backgroundColor: color});
		}
	};

	var append_style_to_iframe = function( ifrm_id, styleElment ) {
		var ifrm = window.frames[ifrm_id];
		ifrm = ( ifrm.contentDocument || ifrm.contentDocument || ifrm.document );
		var head = ifrm.getElementsByTagName( 'head' )[0];

		if ( typeof styleElment !== "undefined" ) {
			head.appendChild( styleElment );
		}
	};

	// Redefines jQuery.fn.html() to add custom events that are triggered before and after a DOM element's innerHtml is changed
	// html-change-pre is triggered before the innerHtml is changed
	// html-change-post is triggered after the innerHtml is changed
	var eventName = 'html-change';
	// Save a reference to the original html function
	jQuery.fn.originalHtml = jQuery.fn.html;
	// Let's redefine the html function to include a custom event
	jQuery.fn.html = function() {
		var currentHtml = this.originalHtml();
		if(arguments.length) {
			this.trigger(eventName + '-pre', jQuery.merge([currentHtml], arguments));
			jQuery.fn.originalHtml.apply(this, arguments);
			this.trigger(eventName + '-post', jQuery.merge([currentHtml], arguments));
			return this;
		} else {
			return currentHtml;
		}
	};

})(jQuery);

function ColorLuminance(hex, lum) {

	// validate hex string
	hex = String(hex).replace(/[^0-9a-f]/gi, '');
	if (hex.length < 6) {
		hex = hex[0]+hex[0]+hex[1]+hex[1]+hex[2]+hex[2];
	}
	lum = lum || 0;

	// convert to decimal and change luminosity
	var rgb = "#", c, i;
	for (i = 0; i < 3; i++) {
		c = parseInt(hex.substr(i*2,2), 16);
		c = Math.round(Math.min(Math.max(0, c + (c * lum)), 255)).toString(16);
		rgb += ("00"+c).substr(c.length);
	}

	return rgb;
}