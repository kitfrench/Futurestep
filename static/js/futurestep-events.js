$(function(){

    $('.events-for-month .chronological li .event-detail').hide();
    $('.events-for-month .chronological li .controls .contract').hide();
    $('.events-for-month .chronological li .controls .expand').show();

    $('.events-for-month .chronological li .expand').click(function(e){
        e.preventDefault();
        $(this).parent().parent().find('.event-detail').slideDown();
        /* load content here if required - using the code below
        var permalink = $(this).parent().parent().find('.gotopost').attr('href');
        console.log(permalink);
        */
        $(this).hide();
        $(this).parent().find('.contract').show();
        return false;
    });

    $('.events-for-month .chronological li .controls .contract').click(function(e){
        e.preventDefault();
        $(this).parent().parent().find('.event-detail').slideUp();
        $(this).hide();
        $(this).parent().find('.expand').show();
        return false;
    });
});