jQuery(document).ready(function($) {

    $('form#post').on('submit', function(e) {
        if ($('#titlediv input').val() === '') {
            e.preventDefault();
            alert(wdf.title_remind);
            return false;
        }
    });


    $('#wdf_type input').on('change', function(e) {
        if ($(this).val() == 'simple') {
            $('#wdf_has_goal').prop("disabled", false).val('0').trigger("change");
            $('#wdf_recurring').show();
        } else {
            $('#wdf_has_goal').prop("disabled", true).val('1').trigger("change");
            $('#wdf_recurring').hide();
        }
    });


    var dates = $("#wdf_goal_start_date, #wdf_goal_end_date").datepicker({
        dateFormat: 'yy-mm-dd',
        changeMonth: true,
        numberOfMonths: 2,
    });

    //$('.wdf_level .wdf_check_switch').live('change', function(e) {
    //	if($(this).is(':checked')) {
    //		$(this).parents('.wdf_level').next('tr').find('div.wdf_reward_toggle').slideDown(400);
    //	} else {
    //		$(this).parents('.wdf_level').next('tr').find('div.wdf_reward_toggle').slideUp(400);
    //	}
    //});

    //Fire fixDelete() on load
    fixDelete();

    //Delete Level Line Item
    $('#wdf_levels_table').on('click', "#wdf_add_level", function(e) {
        e.preventDefault();
        var current = returnNameIndex($('#wdf_levels_table tr.wdf_level.last').find('input:first').attr('name'));
        var newi = parseInt(current) + 1;
        $('#wdf_level_index').val(parseInt($('#wdf_level_index').val()) + 1);
        var template = $('tr[rel="wdf_level_template"]').clone().removeAttr('rel').show();
        var level = template.filter('tr:first').addClass('wdf_level');
        //Replace the name for all inputs with the appropriate index
        $.each($(template).find(':input'), function(i, e) {
            var rel = $(e).attr('rel');
            $(e).attr('name', rel.replace('wdf[levels][', 'wdf[levels][' + String(newi)))
            $(e).removeAttr('rel');
        });
        $('tr[rel="wdf_level_template"]:first').before(template);
        fixDelete();

        $('.wdf_level.last .delete a').on("click", function(e) {
            e.preventDefault();
            var reward = $(this).parents('tr.wdf_level.last').next('tr.wdf_reward_options');
            $(this).parents('tr.wdf_level.last').add(reward).remove();
            fixDelete();
        });
        return false;
    });
    $('#wdf_levels_table').on('click', ".wdf_level.last .delete a", function(e) {
        e.preventDefault();
        var reward = $(this).parents('tr.wdf_level.last').next('tr.wdf_reward_options');
        $(this).parents('tr.wdf_level.last').add(reward).remove();
        fixDelete();
        return false;
    });

    $('.postbox-container').on('click', "#tooltip_submit", function() {
        $('#publish').trigger('click');
        return false;
    });

    function fixDelete() {
        if ($('#wdf_levels_table tbody .wdf_level').length < 1) {
            return false;
        }
        $('#wdf_levels_table tbody .wdf_level').removeClass('last');
        $('#wdf_levels_table tbody .wdf_level:last').addClass('last');
        fixInputs();
    }

    function returnNameIndex(string) {
        if ($('#wdf_levels_table tr.wdf_level').length > 9)
            return string.substr(12, 2);
        else
            return string.substr(12, 1);
    }

    // Hover effect
    $('.wdf_actvity_level').on('mouseenter', function() {
        $(this).find('td:last a').show();
    }).on('mouseleave', function() {
        $(this).find('td:last a').hide();
    });

    // Progress bar initialization
    $('.wdf_goal_progress').progressbar({
        value: 0,
        create: function() {
            $(this).progressbar("option", "value", Math.round(parseInt($(this).attr('total') * 100) / parseInt($(this).attr('goal'))));
        }
    });

    function fixInputs() {
        var input_switches = $('.wdf_input_switch');

        $.each(input_switches, function(i, elm) {
            if (elm.localName == 'textarea') {
                var current = $(elm).html();
            } else if (elm.localName == 'input') {
                var current = $(elm).val();
            }
            $(elm).on('focusin focusout', function(e) {

                if (e.type == 'focusout') {
                    $(elm).prev('.wdf_bignum').addClass('wdf_disabled');
                } else {
                    $(elm).prev('.wdf_bignum').removeClass('wdf_disabled');
                }
            });
        });
    }
    //run fix_inputs() on load
    fixInputs();

    $('select.wdf_toggle').on('change', function(e) {
        var rel = $(this).attr('rel');
        var val = $(this).val();
        if (rel == 'wdf_has_goal' && val == '1') {
            var elm = $('*[rel="' + rel + '"]').not(this);
            elm.show();
        } else if (rel == 'wdf_has_reward') {
            var elm = $('*[rel="' + rel + '"]').not(this);
            if (val == '1') {
                elm.show();
            } else {
                elm.hide();
            }
        } else if (rel == 'wdf_collect_address') {
            var elm = $('*[rel="' + rel + '"]').not(this);
            if (val == '1') {
                elm.show();
            } else {
                elm.hide();
            }
        } else if (rel == 'wdf_thanks_type') {
            $('*[rel="' + rel + '"]').not(this).hide(1, function() {
                $('.wdf_thanks_' + val + '[rel="' + rel + '"]').show();
            });
        } else if (rel == 'wdf_has_goal' && val == '0') {
            var elm = $('*[rel="' + rel + '"]').not(this);
            elm.hide();
        } else if (rel == 'wdf_send_email') {
            if (val == '1')
                $('*[rel="' + rel + '"]').not(this).show();
            else
                $('*[rel="' + rel + '"]').not(this).hide();
        } else if (rel == 'wdf_recurring') {
            if (val == 'yes')
                $('*[rel="' + rel + '"]').not(this).show();
            else
                $('*[rel="' + rel + '"]').not(this).hide();
        }
    });

});