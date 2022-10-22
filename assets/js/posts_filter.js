jQuery(window).on("elementor/frontend/init", function() {
    jQuery(".posts-filter--type-archive a").click(function(e) {
        e.preventDefault();
        const data = build_query_parameters(jQuery(this), "html");
        const URI = window.location.href.split('?')[0];
        if (jQuery(this).closest(".elementor-element").hasClass("posts-filter--ajax-yes")) {
            data.is_ajax = 1;
            render_ajax_query(data, URI);
        }else{
            const params = jQuery.param(data);
            location.href = URI + "?" + params;
        }
    });

    jQuery(".posts-filter--type-archive select").change(function(e) {
        e.preventDefault();
        const data = build_query_parameters(jQuery(this), "option");
        const URI = window.location.href.split('?')[0];
        if (jQuery(this).closest(".elementor-element").hasClass("posts-filter--ajax-yes")) {
            data.is_ajax = 1;
            render_ajax_query(data, URI);
        }else{
            const params = jQuery.param(data);
            location.href = URI + "?" + params;
        }
    });

    jQuery(".posts-filter--type-category a").click(function(e) {
        e.preventDefault();
        const data = build_query_parameters(jQuery(this), "html");
        const URI = window.location.href.split('?')[0];
        if (jQuery(this).closest(".elementor-element").hasClass("posts-filter--ajax-yes")) {
            data.is_ajax = 1;
            render_ajax_query(data, URI);
        }else{
            const params = jQuery.param(data);
            location.href = URI + "?" + params;
        }
    });

    jQuery(".posts-filter--type-category select").change(function(e) {
        e.preventDefault();
        const data = build_query_parameters(jQuery(this), "option");
        const URI = window.location.href.split('?')[0];
        if (jQuery(this).closest(".elementor-element").hasClass("posts-filter--ajax-yes")) {
            data.is_ajax = 1;
            render_ajax_query(data, URI);
        }else{
            data.target_query_id = "";
            const params = jQuery.param(data);
            location.href = URI + "?" + params;
        }
    });

    function render_ajax_query(data, uri) {
        jQuery(`#${data.target_query_id}`).html("");
        jQuery.ajax({
            url: uri,
            method: "GET",
            data: data,
            dataType: "html",
            success: function(resp) {
                jQuery(`#${data.target_query_id}`).html(jQuery(resp).find(`#${data.target_query_id}`));
            }
        });
    }

    function build_query_parameters($el, type) {
        var month_year = (type == "html") ? $el.attr("href").match(/(\/[0-9]\/?)\d+/g) : $el.val().match(/(\/[0-9]\/?)\d+/g);
        const data = {
            term_id: (type == "html") ? $el.data("termid") : $el.val(),
            target_query_id: $el.closest("div").data("targetid"),
            taxonomy: $el.closest("div").data("taxonomy"),
            month: month_year && month_year[1] ? month_year[1].replace(/\D/g,'') : "",
            _year: month_year ? month_year[0].replace(/\D/g,'') : "",
            action: "filter_posts_widget"
        };
        return data;
    }
});