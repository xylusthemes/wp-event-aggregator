(function( $ ) {
	'use strict';

	jQuery(document).ready(function(){
		jQuery('.xt_datepicker').datepicker({
			changeMonth: true,
			changeYear: true,
			dateFormat: 'yy-mm-dd'
		});
		jQuery(document).on("click", ".wpea_datepicker", function(){
		    jQuery(this).datepicker({
				changeMonth: true,
				changeYear: true,
				dateFormat: 'yy-mm-dd',
				showOn:'focus'
			}).focus();
		});

		jQuery(document).on("click", ".vc_ui-panel .wpea_datepicker input[type='text']", function(){
		    jQuery(this).datepicker({
				changeMonth: true,
				changeYear: true,
				dateFormat: 'yy-mm-dd',
				showOn:'focus'
			}).focus();
		});
	});

	jQuery(document).ready(function(){
        jQuery('#wpea_meetup_import_by').on('change', function(){
    
            if( jQuery(this).val() == 'event_id' ){
                jQuery('.import_type_wrapper').hide();
                jQuery('.meetup_group_url').hide();
                jQuery('.meetup_group_url .meetup_url').removeAttr( 'required' );
                jQuery('.meetup_event_id').show();
                jQuery('.meetup_event_id .ime_event_ids').attr('required', 'required');
            
            }else if( jQuery(this).val() == 'group_url' ){
                jQuery('.import_type_wrapper').show();
                jQuery('.meetup_group_url').show();
                jQuery('.meetup_group_url .meetup_url').attr('required', 'required');
                jQuery('.meetup_event_id').hide();
                jQuery('.meetup_event_id .ime_event_ids').removeAttr( 'required' );
            }
        });
    
        jQuery('#import_type').on('change', function(){
            if( jQuery(this).val() != 'onetime' ){
                jQuery('.hide_frequency .import_frequency').show();
            }else{
                jQuery('.hide_frequency .import_frequency').hide();
            }
        });
    
        jQuery("#import_type").trigger('change');
        jQuery("#wpea_meetup_import_by").trigger('change');
    });
	
	jQuery(document).ready(function(){
		jQuery('#eventbrite_import_by').on('change', function(){

			if( jQuery(this).val() == 'event_id' ){
				jQuery('.import_type_wrapper').hide();
				jQuery('.eventbrite_organizer_id').hide();
				jQuery('.eventbrite_organizer_id .wpea_organizer_id').removeAttr( 'required' );
				jQuery('.eventbrite_event_id').show();
				jQuery('.eventbrite_event_id .wpea_eventbrite_id').attr('required', 'required');

			} else if( jQuery(this).val() == 'your_events' ){
				jQuery('.import_type_wrapper').show();
				jQuery('.eventbrite_organizer_id').hide();
				jQuery('.eventbrite_organizer_id .wpea_organizer_id').removeAttr( 'required' );
				jQuery('.eventbrite_event_id').hide();
				jQuery('.eventbrite_event_id .wpea_eventbrite_id').removeAttr( 'required' );

			} else if( jQuery(this).val() == 'organizer_id' ){
				jQuery('.import_type_wrapper').show();
				jQuery('.eventbrite_organizer_id').show();
				jQuery('.eventbrite_organizer_id .wpea_organizer_id').attr('required', 'required');
				jQuery('.eventbrite_event_id').hide();
				jQuery('.eventbrite_event_id .wpea_eventbrite_id').removeAttr( 'required' );
			
			}

		});

		jQuery('#import_type').on('change', function(){
			if( jQuery(this).val() != 'onetime' ){
				jQuery('.hide_frequency .import_frequency').show();
			}else{
				jQuery('.hide_frequency .import_frequency').hide();
			}
		});

		jQuery("#import_type").trigger('change');
		jQuery("#eventbrite_import_by").trigger('change');
	});	


	jQuery(document).ready(function(){
		jQuery(document).on('change', '#facebook_import_by', function(){
			var current_value = jQuery(this).val();
			
			if( current_value == 'facebook_event_id' ){
				jQuery('.import_type_wrapper').hide();

				jQuery('.facebook_page_wrapper').hide();
				jQuery('.facebook_page_wrapper .facebook_page_username').removeAttr( 'required' );

				jQuery('.facebook_group_wrapper').hide();
				jQuery('.facebook_group_wrapper .facebook_group').removeAttr( 'required' );

				jQuery('.facebook_eventid_wrapper').show();
				jQuery('.facebook_eventid_wrapper .facebook_event_ids').attr('required', 'required');

			} else if( current_value == 'facebook_group' ){
				jQuery('.import_type_wrapper').show();

				jQuery('.facebook_eventid_wrapper').hide();
				jQuery('.facebook_eventid_wrapper .facebook_event_ids').removeAttr( 'required' );

				jQuery('.facebook_page_wrapper').hide();
				jQuery('.facebook_page_wrapper input.facebook_page_username').removeAttr( 'required' );

				jQuery('.facebook_group_wrapper').show();
				jQuery('.facebook_group_wrapper .facebook_group').attr('required', 'required');

			} else if( current_value == 'facebook_organization' ){

				jQuery('.import_type_wrapper').show();

				jQuery('.facebook_eventid_wrapper').hide();
				jQuery('.facebook_eventid_wrapper .facebook_event_ids').removeAttr( 'required' );

				jQuery('.facebook_group_wrapper').hide();
				jQuery('.facebook_group_wrapper .facebook_group').removeAttr( 'required' );

				jQuery('.facebook_page_wrapper').show();
				jQuery('.facebook_page_wrapper .facebook_page_username').attr('required', 'required');
			}

		});

		jQuery("#facebook_import_by").trigger('change');
	});	


	jQuery(document).ready(function(){
		jQuery('#ical_import_by').on('change', function(){

			if( jQuery(this).val() == 'ical_url' ){
				jQuery('.import_type_wrapper').show();
				jQuery('.ical_url_wrapper').show();
				jQuery('.ical_url_wrapper .ical_url').attr('required', 'required');
				jQuery('.ics_file_wrapper').hide();
				jQuery('.ics_file_wrapper .ics_file_class').removeAttr( 'required' );

			} else if( jQuery(this).val() == 'ics_file' ){
				jQuery('.import_type_wrapper').hide();
				jQuery('.ics_file_wrapper').show();
				jQuery('.ics_file_wrapper .ics_file_class').attr('required', 'required');
				jQuery('.ical_url_wrapper').hide();
				jQuery('.ical_url_wrapper .ical_url').removeAttr( 'required' );

			}

		});

		jQuery("#ical_import_by").trigger('change');
	});

	jQuery(document).ready(function(){
		jQuery('#ical_import_by_date').on('change', function(){
			if( jQuery(this).val() == 'custom_date_range' ){
				jQuery('#custom_date_range_se').show();
			}else{
				jQuery('#custom_date_range_se').hide();
			}
		});
		jQuery("#ical_import_by_date").trigger('change');
	});

	// Render Dynamic Terms.
	jQuery(document).ready(function() {
	    jQuery('.event_plugin').on( 'change', function() {

	    	var event_plugin = jQuery(this).val();
	    	var taxo_cats = jQuery('#wpea_taxo_cats').val();
	    	var taxo_tags = jQuery('#wpea_taxo_tags').val();
	    	var data = {
	            'action': 'wpea_render_terms_by_plugin',
	            'event_plugin': event_plugin,
	            'taxo_cats': taxo_cats,
	            'taxo_tags': taxo_tags
	        };

	        var terms_space = jQuery('.event_taxo_terms_wraper');
	        terms_space.html('<span class="spinner is-active" style="float: none;"></span>');
	        // send ajax request.
	        jQuery.post(ajaxurl, data, function(response) {
	            if( response != '' ){
	            	terms_space.html( response );
	            }else{
	            	terms_space.html( '' );
	            }	            
	        });    
	    });
	    jQuery(".event_plugin").trigger('change');                  
	});

	// Color Picker
    jQuery(document).ready(function($) {
        $('.wpea_color_field').each(function() {
            $(this).wpColorPicker();
        });
	});

	//Shortcode Copy Text
	jQuery(document).ready(function($){
		$(document).on("click", ".wpea-btn-copy-shortcode", function() { 
			var trigger = $(this);
			$(".wpea-btn-copy-shortcode").removeClass("text-success");
			var $tempElement = $("<input>");
			$("body").append($tempElement);
			var copyType = $(this).data("value");
			$tempElement.val(copyType).select();
			document.execCommand("Copy");
			$tempElement.remove();
			$(trigger).addClass("text-success");
			var $this = $(this),
			oldText = $this.text();
			$this.attr("disabled", "disabled");
			$this.text("Copied!");
			setTimeout(function(){
				$this.text( oldText );
				$this.removeAttr("disabled");
			}, 800);
	  
		});

	});
	
	// ticket section 
	jQuery(document).ready(function() {
	    jQuery('.enable_ticket_sec').on( 'change', function() {
			var ischecked= jQuery(this).is(':checked');
			if(ischecked){
				jQuery('.checkout_model_option').show();
			}else{
				jQuery('.checkout_model_option').hide();
			}
	    });
	    jQuery(".enable_ticket_sec").trigger('change');
	});	
})( jQuery );

jQuery(document).ready(function($){

	const wpea_tab_links = document.querySelectorAll('.wpea-setting-tab');
	const wpea_tabcontents = document.querySelectorAll('.wpea-setting-tab-child');

	wpea_tab_links.forEach(function(link) {
		link.addEventListener('click', function() {
			const wpea_tabId = this.dataset.tab;

			wpea_tab_links.forEach(function(link) {
				link.classList.remove('active');
			});

			wpea_tabcontents.forEach(function(content) {
				content.classList.remove('active');
			});

			this.classList.add('active');
			document.getElementById(wpea_tabId).classList.add('active');
		});
	});

	const wpea_gm_apikey_input = document.querySelector('.wpea_google_maps_api_key');
	if ( wpea_gm_apikey_input ) {
		wpea_gm_apikey_input.addEventListener('input', function() {
			const wpea_check_key = document.querySelector('.wpea_check_key');
			if (wpea_gm_apikey_input.value.trim() !== '') {
				wpea_check_key.style.display = 'contents';
			} else {
				wpea_check_key.style.display = 'none';
			}
		});
	}

	const wpea_checkkeylink = document.querySelector('.wpea_check_key a');
	if ( wpea_checkkeylink ) { 
		wpea_checkkeylink.addEventListener('click', function(event) { 
			event.preventDefault(); 
			const wpea_gm_apikey = wpea_gm_apikey_input.value.trim(); 
			if ( wpea_gm_apikey !== '' ) { 
				wpea_check_gmap_apikey(wpea_gm_apikey); 
			} 
		}); 
	}

	function wpea_check_gmap_apikey(wpea_gm_apikey) {
		const wpea_xhr = new XMLHttpRequest();
		wpea_xhr.open('GET', 'https://www.google.com/maps/embed/v1/place?q=New+York&key=' + encodeURIComponent(wpea_gm_apikey), true);
		const wpea_loader = document.getElementById('wpea_loader');
		wpea_loader.style.display = 'inline-block';
		wpea_xhr.onreadystatechange = function() {
			if ( wpea_xhr.readyState === XMLHttpRequest.DONE ) {
				wpea_loader.style.display = 'none';
				if (wpea_xhr.status === 200) {
					const response = wpea_xhr.responseText;
					var wpea_gm_success_notice = jQuery("#wpea_gmap_success_message");
						wpea_gm_success_notice.html('<span class="wpea_gmap_success_message">Valid Google Maps License Key</span>');
						setTimeout(function(){ wpea_gm_success_notice.empty(); }, 2000);
				} else {
					var wpea_gm_error_notice = jQuery("#wpea_gmap_error_message");
					wpea_gm_error_notice.html( '<span class="wpea_gmap_error_message" >Inalid Google Maps License Key</span>' );
						setTimeout(function(){ wpea_gm_error_notice.empty(); }, 2000);
				}
			}
		};

		wpea_xhr.send();
	}

	var mediaUploader;
	$('#wpea-choose-from-library-button').click(function(e) {
		e.preventDefault();
		if (mediaUploader) {
			mediaUploader.open();
			return;
		}
		mediaUploader = wp.media.frames.file_frame = wp.media({
			title: 'Choose Event Thumbnail',
			button: {
				text: 'Choose Event Thumbnail'
			},
			multiple: false
		});

		mediaUploader.on('select', function() {
			var attachment = mediaUploader.state().get('selection').first().toJSON();
			$('#wpea-event_thumbnail_hidden_field').val(attachment.id);
			$('#wpea-event-thumbnail-img').attr('src', attachment.url);
			$('#wpea-event-thumbnail-preview').removeClass('hidden');
			$('#wpea-js-remove-thumbnail').removeClass('hidden');
			$('#wpea-choose-from-library-button').text('Change Event Thumbnail');
		});

		mediaUploader.open();
	});

	$('#wpea-js-remove-thumbnail').click(function(e) {
		e.preventDefault();
		$('#wpea-event_thumbnail_hidden_field').val('');
		$('#wpea-event-thumbnail-img').attr('src', '');
		$('#wpea-event-thumbnail-preview').addClass('hidden');
		$('#wpea-js-remove-thumbnail').addClass('hidden');
		$('#wpea-choose-from-library-button').text('Choose Event Thumbnail');
	});
});


