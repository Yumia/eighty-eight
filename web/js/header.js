jQuery(document).ready(function($){

    //open sub-navigation
    $('#overlay, .nav-opener').on('click', function(event){
        event.preventDefault();
        $('.track').toggleClass('moves-out');
        $('.nav-opener').toggleClass('close-nav');
        $('#overlay').toggleClass('hide');
    });

});