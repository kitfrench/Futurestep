/**
 * Date: 12/08/2011
 * To change this template use File | Settings | File Templates.
 */

if($){

  var fs = {};
  
  fs.view = {
    servicesnav : null,     
    ataglancePanel : null,
    init: function(){
      this.servicesnav = $('#services-navigation');
      this.ataglancePanel = $('#at-a-glance-details');
      
      this.servicesnav.hide();
      this.ataglancePanel.hide();
    },
    
    toggleServiceNav : function(){
      
      function changeArrow(){
        var $servicesEl = $('#services span.decoration');
        var rightArrowState = $servicesEl.hasClass('right-arrow');
      
        if(rightArrowState){
          $servicesEl.removeClass('right-arrow');
          $servicesEl.addClass('down-arrow');
        }
        else{
          $servicesEl.removeClass('down-arrow');
          $servicesEl.addClass('right-arrow');
        }
      }
      
      this.servicesnav.fadeToggle('fast', changeArrow());  
    },
    
    toggleAtAGlance : function(){
      this.ataglancePanel.slideToggle('fast', function(){
      $.scrollTo('max', 300);
      });
    }
  };
  
  fs.controller = {
    init: function(){
      fs.view.init();
   
      $('#services').click(function(e){
          e.preventDefault();
          fs.view.toggleServiceNav();
          return false;
        });
      
      $('#at-a-glance').click(function(e){
          e.preventDefault();
          fs.view.toggleAtAGlance();          
          return false;        
      })
      
    }
  };
  
  $(function(){

    fs.controller.init();
    
  });  
}
