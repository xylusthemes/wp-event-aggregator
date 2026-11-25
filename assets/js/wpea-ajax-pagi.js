jQuery(document).ready(function($){
    $(document).on('click', '.prev-next-posts a', function(e){
        var $link = $(this);
        var $container = $link.closest('.wpea_frontend_archive'); // old container
        var atts = $container.data('shortcode');       // shortcode attributes
        var nextPage = parseInt($link.data('page')) || 1;

        if (!atts || atts.ajaxpagi !== 'yes') return true;

        e.preventDefault();
        $container.addClass('wpea-loading');

        $.ajax({
            url: wpea_ajax.ajaxurl,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'wpea_load_paged_events',
                atts: JSON.stringify(atts),
                page: nextPage
            },
            success: function(response){
                if(response.success){
                    // Replace old container with new HTML
                    $container.replaceWith(response.data);

                    // Update $container reference for next click
                    var $newContainer = $('.wpea_frontend_archive').filter(function(){
                        return $(this).data('shortcode').ajaxpagi === 'yes';
                    }).first();

                    // Update pagination links dynamically
                    $newContainer.find('.wpea-next-page').attr('data-page', nextPage + 1);
                    $newContainer.find('.wpea-prev-page').attr('data-page', nextPage - 1);

                    // Re-run live sold out check for newly loaded items
                    wpea_run_live_sold_out_check($newContainer);
                }
            },
            complete: function(){
                $container.removeClass('wpea-loading');
            }
        });

    });

    // Run Live Sold Out Check on Page Load
    wpea_run_live_sold_out_check();
});

function wpea_run_live_sold_out_check($context) {
    if (typeof wpea_ajax === 'undefined' || !wpea_ajax.ajaxurl || wpea_ajax.live_check_enabled !== 'yes') return;
    
    $context = $context || jQuery(document);
    var event_ids = [];
    $context.find('.archive-event, .wpea_event_container, .wpea_event_list, .iee6_event-card, .wpea6_event-card, article, [class*="post-"]').each(function() {
        var $el = jQuery(this);
        var classes = $el.attr('class');
        if (!classes) return;
        var match = classes.match(/\bpost-(\d+)\b/);
        if (match && match[1]) {
            var id = match[1];
            if (event_ids.indexOf(id) === -1) {
                event_ids.push(id);
                $el.attr('data-event-id', id);
            }
        }
    });

    if (event_ids.length > 0) {
        jQuery.ajax({
            url: wpea_ajax.ajaxurl,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'wpea_check_live_sold_out',
                event_ids: event_ids
            },
            success: function(response){
                if(response.success && response.data && response.data.sold_out_ids) {
                    var sold_out_ids = response.data.sold_out_ids;
                    jQuery.each(sold_out_ids, function(index, id) {
                        var $event = $context.find('.post-' + id);
                        if ($event.length) {
                            var $imgContainer = $event.find('.img_placeholder, .wpea_img_placeholder, .iee_img_placeholder, .event_img, .wpea-event-img, .iee-event-img, .wpea-style4-event-image-img, .iee-style4-event-image-img, .iee5_event-image, .wpea5_event-image, .iee6_event-image, .wpea6_event-image');
                            if ($imgContainer.length === 0) {
                                $imgContainer = $event.find('.wpea-card, .wpea_widget_style1, .wepa_event, .wpea_event_style2');
                            }
                            var $wrapper = $imgContainer.find('.wpea-badges-wrapper, .iee-badges-wrapper');
                            if ($wrapper.length === 0) {
                                $wrapper = jQuery('<div class="wpea-badges-wrapper iee-badges-wrapper"></div>');
                                $imgContainer.prepend($wrapper);
                            }
                            if ($wrapper.find('.wpea-badge, .iee-badge').filter(function(){ return jQuery(this).text().trim() === 'Sold Out'; }).length === 0) {
                                $wrapper.prepend('<span class="wpea-badge iee-badge">Sold Out</span>');
                            }
                        }
                    });
                }
            }
        });
    }
}