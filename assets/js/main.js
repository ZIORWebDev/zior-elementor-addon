jQuery(window).on("elementor/frontend/init", function() {
    jQuery(".elementor-search-form--ajax-load-yes form").submit(function(e) {
        e.preventDefault();
        const data = {
            keyword: jQuery(this).find("input[name='s']").val(),
            target_query_id: jQuery(this).find("input[name='target_query_id']").val(),
            action: "filter_posts_widget"
        };

        jQuery.ajax({
            url: jQuery(this)[0].baseURI,
            method: "GET",
            data: data,
            dataType: "html",
            success: function(resp) {
                jQuery(`#${data.target_query_id}`).html(jQuery(resp).find(`#${data.target_query_id}`));
            }
        });
    });
});
