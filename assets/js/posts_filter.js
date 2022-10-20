jQuery(".elementor-archive-filter--ajax-load-yes a").click(function(e) {
    e.preventDefault();
    const data = {
        term_id: jQuery(this).data("termid"),
        target_query_id: jQuery(this).closest("div").data("targetid"),
        taxonomy: jQuery(this).closest("div").data("taxonomy"),
        post_date: jQuery(this).text(),
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