jQuery(window).on("elementor/frontend/init", function() {
    jQuery(".elementor-archive-filter--ajax-load-yes a").click(function(e) {
        e.preventDefault();
        var month_year = jQuery(this).attr("href").match(/(\/[0-9]\/?)\d+/g);
        const data = {
            term_id: jQuery(this).data("termid"),
            target_query_id: jQuery(this).closest("div").data("targetid"),
            taxonomy: jQuery(this).closest("div").data("taxonomy"),
            month: month_year && month_year[1] ? month_year[1].replace(/\D/g,'') : "",
            year: month_year ? month_year[0].replace(/\D/g,'') : "",
            action: "filter_posts_widget"
        };

        jQuery(`#${data.target_query_id}`).html("");
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

    jQuery(".elementor-archive-filter--ajax-load-yes select").change(function(e) {
        e.preventDefault();
        var month_year = jQuery(this).val().match(/(\/[0-9]\/?)\d+/g);
        const data = {
            term_id: jQuery(this).data("termid"),
            target_query_id: jQuery(this).closest("div").data("targetid"),
            taxonomy: jQuery(this).closest("div").data("taxonomy"),
            month: month_year && month_year[1] ? month_year[1].replace(/\D/g,'') : "",
            year: month_year ? month_year[0].replace(/\D/g,'') : "",
            action: "filter_posts_widget"
        };

        jQuery(`#${data.target_query_id}`).html("");
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