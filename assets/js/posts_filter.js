jQuery(window).on("elementor/frontend/init", function() {
    jQuery(".posts-filter--type-archive a").click(function(e) {
        e.preventDefault();
        const data = build_query_parameters(jQuery(this), "html", "archive");
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
        const data = build_query_parameters(jQuery(this), "option", "archive");
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
        const data = build_query_parameters(jQuery(this), "html", "category");
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
        const data = build_query_parameters(jQuery(this), "option", "category");
        const URI = window.location.href.split('?')[0];
        if (jQuery(this).closest(".elementor-element").hasClass("posts-filter--ajax-yes")) {
            data.is_ajax = 1;
            render_ajax_query(data, URI);
        }else{
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

    function build_query_parameters($el, display, type) {
        var _month = "";
        var _year = "";
        if (type == "archive") {
            const archive_url = (display == "option") ? $el.val() : $el.attr("href");
            const urlParams = new URLSearchParams(archive_url.split("?")[1]);
            _month = urlParams.get("_month").replace(/\D/g, "");
            _year = urlParams.get("_year").replace(/\D/g, "")
        }
        
        var termid = "";
        if (type == "category") {
            termid = (display == "option") ? $el.val() : $el.data("termid");
        }
        
        const data = {
            term_id: termid,
            target_query_id: $el.closest("div").data("targetid"),
            taxonomy: $el.closest("div").data("taxonomy"),
            month: _month,
            _year: _year,
            action: "filter_posts_widget"
        };
        console.log("data", data);
        return data;
    }
});