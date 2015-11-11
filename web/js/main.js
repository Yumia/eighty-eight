$(document).ready(function () {

    var cons = '.content-constellations .constellation';

    $(document).on({
        mouseenter : function(){
            $(this).addClass('active');
            $('.text-hv', this).addClass('active');
        },
        mouseleave : function(){
            $(this).removeClass('active');
            $('.text-hv', this).removeClass('active');
        }
    }, cons);

    $(document).on('click', '.content-constellations .filters a', function(e){
        e.preventDefault();
        var target = $(this).attr('data-target');
        $('.content-constellations .filters a').removeClass('current');
        $(this).addClass('current');

        $('#viewer').find('.constellation').hide();
        $('#viewer').find('.constellation.'+target).show();
    });

    var $seeAll = $("#see-all");
    var $linkSeeAll = $('a.link-trigger', $seeAll);

    $linkSeeAll.on('click', function(e){
        e.preventDefault();

        if(!$seeAll.hasClass('loaded')){
            $.ajax({
                url: site + "/constellations/list/all/ajax",
                dataType: 'html',
                success: function (data) {
                    $seeAll.addClass('loaded');
                    $('.inner-data', $seeAll).append(data).hide().fadeIn();
                }
            });
        }

        if($seeAll.hasClass('open')){
            $('body').css('overflow', 'auto');
            $seeAll.removeClass('open');
            $linkSeeAll.addClass('trigger-open').removeClass('trigger-close').text('See all constellations');
        } else {
            $('body').css('overflow', 'hidden');
            $seeAll.addClass('open');
            $linkSeeAll.removeClass('trigger-open').addClass('trigger-close').text('Close');
        }

    });

    var $svgContainer = $('.svg-container');
    var $svgHorizon = $('#svg-horizon');
    var $svgMap = $('#svg-map');

    Horizon.init($svgContainer, $svgHorizon);
    Map.init($svgMap);

});