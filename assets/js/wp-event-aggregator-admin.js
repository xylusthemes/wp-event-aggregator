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
				$this.text("Copy");
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


