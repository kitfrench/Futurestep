$(function(){
  $('select[name="sector"]').bind('change', function(){
    $('select[name="service"]').val('');
  });
  
  $('select[name="service"]').bind('change', function(){
    $('select[name="sector"]').val('');
  });
});
