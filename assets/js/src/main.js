jQuery(window).on("elementor/frontend/init", function() {
    jQuery(".elementor-search-form--ajax-load-yes form").submit(function(e) {
        e.preventDefault();
        var target_query_id = jQuery(this).parent().parent().data("query_id");
        const data = {
            keyword: jQuery(this).find("input[name='s']").val(),
            is_ajax: 1,
            action: "filter_posts_widget"
        };

        var $el = jQuery(`[data-query-id="${target_query_id}"]`);
        $el.html("");
        jQuery.ajax({
            url: jQuery(this)[0].baseURI,
            method: "GET",
            data: data,
            dataType: "JSON",
            success: function(resp) {
                $el.html(jQuery(resp.data));
            }
        });
    });
});