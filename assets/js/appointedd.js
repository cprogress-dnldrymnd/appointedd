function custom_option($this) {
    $this.parents(".custom-select-wrapper").find("select").val($this.data("value"));
    $this.parents(".custom-options").find(".custom-option").removeClass("selection");
    $this.addClass("selection");
    $this.parents(".custom-select").removeClass("opened");
    $this.parents(".custom-select").find(".custom-select-trigger").text($this.text());
}
var getUrlParameter = function getUrlParameter(sParam) {
    var sPageURL = window.location.search.substring(1),
        sURLVariables = sPageURL.split('&'),
        sParameterName,
        i;

    for (i = 0; i < sURLVariables.length; i++) {
        sParameterName = sURLVariables[i].split('=');

        if (sParameterName[0] === sParam) {
            return sParameterName[1] === undefined ? true : decodeURIComponent(sParameterName[1]);
        }
    }
    return false;
};
(function ($) {

    jQuery(document).on("click", '.page-link', function (e) {
        jQuery('html, body').animate({
            scrollTop: jQuery("#appointedd-celebrants").offset().top
        }, 500);
        e.preventDefault();
    });
    jQuery(document).on("click", '.page-item', function (e) {
        jQuery('html, body').animate({
            scrollTop: jQuery("#appointedd-celebrants").offset().top
        }, 500);
        e.preventDefault();
    });

    var numberposts = 100;
    var loading = function ($element) {
        $loading = $("<div class='lds-ring'><div></div><div></div><div></div><div></div></div>");
        $element.html($loading);
    }

    var SetCustomSelectItems = function ($element) {
        $selects = $(".custom-select");

        if (typeof $elements !== "undefined") {
            $selects = $element;
        }

        $selects.each(function () {
            var classes = $(this).attr("class"),
                id = $(this).attr("id"),
                name = $(this).attr("name"),
                disable = $(this).attr("disabled-dropdown") == "true";

            var selected = $('option[selected]').text();

            var template = '<div class="' + classes + '">';
            if (disable) {
                template += '<span class="custom-select-trigger disabled">' + selected + '</span>';
            }
            else {
                template += '<span class="custom-select-trigger">' + $(this).attr("placeholder") + '</span>';
            }

            template += '<div class="custom-options">';
            $(this).find("option").each(function () {
                template += '<span class="custom-option ' + $(this).attr("class") + '" data-value="' + $(this).attr("value") + '">' + $(this).html() + '</span>';
            });

            template += '</div></div>';

            $(this).wrap('<div class="custom-select-wrapper"></div>');
            $(this).hide();
            $(this).after(template);
        });

        $(".custom-select-trigger.disabled").css({
            "cursor": "not-allowed",
            "color": "rgba(204, 110, 161, 0.5);"
        });

        $(".custom-option:first-of-type").hover(function () {
            $(this).parents(".custom-options").addClass("option-hover");
        },
            function () {
                $(this).parents(".custom-options").removeClass("option-hover");
            });

        $(".custom-select-trigger").not(".disabled").on("click", function () {
            $('html').one('click', function () {
                $(".custom-select").removeClass("opened");
            });

            $(this).parents(".custom-select").toggleClass("opened");
            event.stopPropagation();
        });

        $(".custom-option").on("click", function () {
            custom_option($(this));
        });
    }



    var showEnquiryForm = function (keep_results_open = false) {
        if (!keep_results_open) {
            $(".filter-results").hide();
        }

        $(".filter-enquire-form").show();
    }

    var setPagination = function ($results, totalPages, visiblePages, onPageClick) {
        $results.twbsPagination({
            totalPages: totalPages,
            visiblePages: visiblePages,
            next: '&#8250;',
            prev: '&#8249;',
            last: '&raquo;',
            first: '&laquo;',
            onPageClick: onPageClick
        });
    }

    var SetActions = function ($results) {
        $results.find('.profile-content').each(function () {
            var $content = $(this);
            var $view_button = $content.siblings('.view-profile-button');
            $content.dialog({
                modal: true,
                autoOpen: false,
                width: '75%',
                show: { effect: "fade", duration: 800 },
                hide: { effect: "fade", duration: 800 },
                close: function (event, ui) {
                    $content.addClass("hidden");
                }
            });

            $content.find('a').attr('target', '_blank');

            $view_button.on('click', function (event) {
                event.preventDefault();
                $content.dialog("open");
            });
        });
    }

    $(document).ready(function () {
        console.log("appointedd init");
        //GetServices();
        //sync_ids();

        var $submit = $('#appointedd-submit-button');
        var $enquire = $('#appointedd-enquire-button');

        $submit.on('click', function (event) {
            if (jQuery('.activate-celebrants-ajax-on-load').length != 0 || jQuery('.activate-celebrants-search').length != 0) {
                event.preventDefault();
                console.log("Submit button clicked");
                $(".filter-enquire-form").hide();
                loading($(".filter-results"));
                $(".filter-results").show();

                $form = $submit.parent('form');
                var data = $form.serialize();
                SearchAvailableIntervals(data);
            }
        });




        $enquire.on("click", function (event) {
            showEnquiryForm();
        });

        $('input.app-display-date').datepicker({
            dateFormat: "dd/mm/yy",
            altField: ".app-date",
            altFormat: "yy-mm-dd",
            nextText: "&#8250;",
            prevText: "&#8249;",
            changeMonth: true,
            changeYear: true,
            minDate: +1,
            maxDate: "+7Y"
        });

        SetCustomSelectItems();
    });

    var GetServices = function (data) {
        var data = {
            limit: 10,
            action: "get_ui_services",
        }
        //url = url + "availability/slots";

        $.ajax({
            url: appointedd_ajaxobj.ajax_url,
            type: "GET",
            data: data,
            success: function (res) {
                console.log("res", res);
                result = JSON.parse(res);
                console.log("result", result);

                var $service_select = $('select[name="service"]');
                var preselected = $service_select.attr("data-preselected-ceremony");

                $service_select.append($("<option></option>"));

                $.each(result, function (index) {
                    console.log(index);
                    var service = this;
                    var $option = $("<option></option>");
                    $option.attr("value", index);
                    if (typeof preselected !== "undefined" && preselected == index) {
                        $option.attr("selected", true);
                        $service_select.attr("disabled", true);
                    }
                    $option.html(this);

                    $service_select.append($option);
                });

                SetCustomSelectItems($service_select);
            },
            error: function (error) {
                console.log("Error", error);
            }
        })
    }

    var SearchAvailableIntervals = function (data) {
        data += "&action=search_available_intervals";
        //url = url + "availability/slots";

        $.ajax({
            url: appointedd_ajaxobj.ajax_url,
            type: "GET",
            data: data,
            success: function (res) {
                var $results = $(".filter-results");
                //$results.twbsPagination("disable");
                //$results.hide();
                $results.html(res.output);

                if (!res.has_results) {
                    showEnquiryForm(true);
                }
                else {
                    var $profiles = $results.find('.profile');
                    $profiles.find('.profile-img img').lazy();
                    var profile_count = $profiles.length;
                    var totalPages = Math.ceil(profile_count / numberposts);
                    $.each($profiles, function (index) {
                        $profile = $(this);
                        var page = Math.floor(index / numberposts) + 1;
                        //$profile.attr("data-page-number", page).css({"visibility": "hidden"});
                        $profile.attr("data-page-number", page).hide();
                    });

                    if ($results.data("twbs-pagination")) {
                        $clonedResults = $results.clone();
                        $results.after($clonedResults);
                        $results.remove();
                        var $results = $clonedResults;
                        $profiles = $results.find('.profile');
                        $profiles.find('.profile-img img').lazy();
                        profile_count = $profiles.length;
                        totalPages = Math.ceil(profile_count / numberposts);
                        //$results.twbsPagination('destroy');
                    }

                    setPagination($results, totalPages, 4, function (event, page) {
                        $profiles.hide();
                        //$results.find('.profile[data-page-number="'+ page +'"]').css({"visibility": "visible"});
                        $results.find('.profile[data-page-number="' + page + '"]').show();

                        //$('#page-content').text('Page ' + page) + ' content here';
                    });

                    SetActions($results);
                }
                //$results.twbsPagination("changeTotalPages", totalPages, 1);

            },
            error: function (error) {
                console.log("Error", error);
            }
        });
    }

    var GetResources = function (data) {
        var data = {
            limit: 10,
            action: "get_available_slots",
        }
        //url = url + "availability/slots";

        $.ajax({
            url: appointedd_ajaxobj.ajax_url,
            type: "GET",
            data: data,
            success: function (res) {
                result = JSON.parse(res);
                console.log("success", result);
            },
            error: function (error) {
                console.log("Error", error);
            }
        })
    }

    var get_resource_by_service = function (data) {
        data += "&action=get_resource_by_service";
        //url = url + "availability/slots";

        $.ajax({
            url: appointedd_ajaxobj.ajax_url,
            type: "GET",
            data: data,
            success: function (res) {
                var $results = $(".filter-results");
                //$results.twbsPagination("disable");
                //$results.hide();
                $results.html(res.output);

                if (!res.has_results) {
                    showEnquiryForm(true);
                }
                else {
                    var $profiles = $results.find('.profile');
                    $profiles.find('.profile-img img').lazy();
                    var profile_count = $profiles.length;
                    var totalPages = Math.ceil(profile_count / numberposts);
                    $.each($profiles, function (index) {
                        $profile = $(this);
                        var page = Math.floor(index / numberposts) + 1;
                        //$profile.attr("data-page-number", page).css({"visibility": "hidden"});
                        $profile.attr("data-page-number", page).hide();
                    });

                    if ($results.data("twbs-pagination")) {
                        $clonedResults = $results.clone();
                        $results.after($clonedResults);
                        $results.remove();
                        var $results = $clonedResults;
                        $profiles = $results.find('.profile');
                        $profiles.find('.profile-img img').lazy();
                        profile_count = $profiles.length;
                        totalPages = Math.ceil(profile_count / numberposts);
                        //$results.twbsPagination('destroy');
                    }

                    /* $results.twbsPagination({
                        totalPages: totalPages,
                        visiblePages: 3,
                        next: 'Next',
                        prev: 'Prev',
                        onPageClick: function (event, page) {
                            $profiles.hide();
                            //$results.find('.profile[data-page-number="'+ page +'"]').css({"visibility": "visible"});
                            $results.find('.profile[data-page-number="'+ page +'"]').show();
                            
                            //$('#page-content').text('Page ' + page) + ' content here';
                        }
                    }); */

                    setPagination($results, totalPages, 4, function (event, page) {
                        $profiles.hide();
                        //$results.find('.profile[data-page-number="'+ page +'"]').css({"visibility": "visible"});
                        $results.find('.profile[data-page-number="' + page + '"]').show();

                        //$('#page-content').text('Page ' + page) + ' content here';
                    });

                    SetActions($results);
                }
            },
            error: function (error) {
                console.log("Error", error);
                $(".filter-results").html(error.responseText);
            }
        })
    }

    var get_all_resource = function (data) {
        data += "&action=get_all_resource";
        //url = url + "availability/slots";

        $.ajax({
            url: appointedd_ajaxobj.ajax_url,
            type: "GET",
            data: data,
            success: function (res) {
                var $results = $(".filter-results");
                //$results.twbsPagination("disable");
                //$results.hide();
                $results.html(res.output);

                if (!res.has_results) {
                    showEnquiryForm(true);
                }
                else {
                    var $profiles = $results.find('.profile');
                    $profiles.find('.profile-img img').lazy();
                    var profile_count = $profiles.length;
                    var totalPages = Math.ceil(profile_count / numberposts);
                    $.each($profiles, function (index) {
                        $profile = $(this);
                        var page = Math.floor(index / numberposts) + 1;
                        //$profile.attr("data-page-number", page).css({"visibility": "hidden"});
                        $profile.attr("data-page-number", page).hide();
                    });

                    if ($results.data("twbs-pagination")) {
                        $clonedResults = $results.clone();
                        $results.after($clonedResults);
                        $results.remove();
                        var $results = $clonedResults;
                        $profiles = $results.find('.profile');
                        $profiles.find('.profile-img img').lazy();
                        profile_count = $profiles.length;
                        totalPages = Math.ceil(profile_count / numberposts);
                        //$results.twbsPagination('destroy');
                    }

                    /* $results.twbsPagination({
                        totalPages: totalPages,
                        visiblePages: 3,
                        next: 'Next',
                        prev: 'Prev',
                        onPageClick: function (event, page) {
                            $profiles.hide();
                            //$results.find('.profile[data-page-number="'+ page +'"]').css({"visibility": "visible"});
                            $results.find('.profile[data-page-number="'+ page +'"]').show();
                            
                            //$('#page-content').text('Page ' + page) + ' content here';
                        }
                    }); */

                    setPagination($results, totalPages, 4, function (event, page) {
                        $profiles.hide();
                        //$results.find('.profile[data-page-number="'+ page +'"]').css({"visibility": "visible"});
                        $results.find('.profile[data-page-number="' + page + '"]').show();

                        //$('#page-content').text('Page ' + page) + ' content here';
                    });

                    SetActions($results);
                }
            },
            error: function (error) {
                console.log("Error", error);
            }
        })
    }

    var sync_ids = function (data) {
        var data = {
            limit: 10,
            action: "sync_ids",
        }
        //url = url + "availability/slots";

        $.ajax({
            url: appointedd_ajaxobj.ajax_url,
            type: "GET",
            data: data,
            success: function (res) {
                result = JSON.parse(res);
                console.log("success", result);
            },
            error: function (error) {
                console.log("Error", error);
            }
        })
    }

})(jQuery);

jQuery(document).ready(function () {
    if (jQuery('.activate-celebrants-ajax-on-load').length != 0) {
        if (getUrlParameter('limit')) {
            jQuery('.similar_Celebrants.is-grid').remove();
            $service = getUrlParameter('service');
            $date = getUrlParameter('date');
            $location = getUrlParameter('location');

            if ($location != false) {
                custom_option(jQuery('.custom-option[data-value="' + $location + '"]'));
            }
            if ($service != false) {
                custom_option(jQuery('.custom-option[data-value="' + $service + '"]'));
                console.log($service);
            }

            if ($date) {
                jQuery('.app-date').val($date);
                jQuery('input[name="display-date"]').val($date);
            }
            jQuery('#appointedd-submit-button').click();
        }
    }
});

