jQuery(window).on("elementor/frontend/init", function() {
    function addHandler($element) {
        const settings = $element.data("settings");
        const swiperConfig = {
            autoplay: {
                "delay": 8000
            },
            direction: "horizontal",
            effect: (settings ? settings.transition : "slide"),
            loop: (settings ? settings.infinite : true),
            pagination: {
                el: ".swiper-pagination",
                type: "fraction"
            },
            scrollbar: {
                draggable: false,
                el: ".swiper-scrollbar"
            },
            speed: (settings ? settings.transition_speed : 500)
        };

        if ("undefined" === typeof Swiper) {
            const asyncSwiper = elementorFrontend.utils
            .swiper;
            new asyncSwiper($element.find(".swiper"),
                swiperConfig).then(function (swiper) {
                updateDraggableControl(1, (parseInt(
                        swiper.slides.length
                        ) - 2), $element);
                swiper.on("slideChange", function () {
                    const slide = swiper
                        .slides[swiper
                            .realIndex + 1];
                    jQuery(".swiper-active-title")
                        .html(jQuery(slide)
                            .data("heading"));
                    updateDraggableControl((
                        parseInt(
                            swiper
                            .realIndex
                            ) + 1), (
                        parseInt(
                            swiper
                            .slides
                            .length) -
                        2), $element);
                });
            });
        } else {
            new Swiper($element.find(".swiper"),
            swiperConfig);
        }
    }

    function updateDraggableControl(index, sliderCount,
        $element) {
        const draggableWidth = ((index / sliderCount) * 100) +
            "%";
        $element.find(".swiper-scrollbar-drag").css({
            minWidth: draggableWidth,
            width: draggableWidth
        });
    }

    elementorFrontend.hooks.addAction(
        "frontend/element_ready/zior_slides.default",
        addHandler);
});