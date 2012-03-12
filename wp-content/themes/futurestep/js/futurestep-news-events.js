$(function(){

    $('.event .event-detail').hide();
    $('.event .controls .button.contract').hide();

    $('.event .controls .button.expand').click(function($el){
        $(this).parent().parent().find('.event-detail').slideDown();
        /* load content here if required - using the code below
        var permalink = $(this).parent().parent().find('.gotopost').attr('href');
        console.log(permalink);
        */
        $(this).hide();
        $(this).parent().find('.button.contract').show();
    });

    $('.event .controls .button.contract').click(function($el){
        $(this).parent().parent().find('.event-detail').slideUp();
        $(this).hide();
        $(this).parent().find('.button.expand').show();
    });
});