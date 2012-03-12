$(function() {

    var heroSlider = {};

    heroSlider.view = (function() {

        return {

            slides : $('#carousel ul.headlines li'),
            slideContainer : $('#carousel ul.headlines'),
            indexItems  : $('#carousel ul.index li'),
            indexLinks  : $('#carousel ul.index li a'),
            prevLink  : $('#carousel a.prev'),
            nextLink  : $('#carousel a.next'),
            slideWidth : $($('#carousel ul.headlines li')[0]).outerWidth(),
            transitionType : 'slide'
        };

    })();

    var jobsSlider = {};
    jobsSlider.view = (function() {

        return {
            slides : $('.jobs-slider ul.jobs li'),
            slideContainer : $('.jobs-slider ul.jobs'),
            indexItems  : $('.jobs-slider ul.slider-index li'),
            indexLinks  : $('.jobs-slider ul.slider-index li a'),
            prevLink  : $('.jobs-slider a.prev-small'),
            nextLink  : $('.jobs-slider a.next-small'),
            slideWidth : $($('.jobs-slider ul.jobs li')[0]).outerWidth(),
            transitionType : 'fade'
        };

    })();

    function SliderController(view, delay, title) {

        this.title = title;
        this.view = view;
        this.slideCount = view.slides.length;
        this.currentSlide = 0;
        this.slideTimeOut = null;
        this.slideInterval = delay;

        function getIndex(attr) {
            var parts = attr.split('/');

            return parseInt(parts[3]);//Hardcoded - will fail if the href changes
        }

        var that = this;
  
        view.prevLink.click(function(e){
          e.preventDefault();
          var index = that.currentSlide -1;

          if(that.currentSlide <= 0){
            index = that.slideCount -1;
          }

          that.gotoItem(index, function() {
                that.setIndex(index);
                that.currentSlide = index;
            });
          
          return false;
        });
      
        view.nextLink.click(function(e){
          e.preventDefault();
          var index = that.currentSlide +1;

          if(that.currentSlide >= that.slideCount-1){
            index = 0;
          }


          that.gotoItem(index, function() {
                that.setIndex(index);
                that.currentSlide = index;
            });
          
          return false;
        });
      
        view.indexLinks.click(function(e) {
            e.preventDefault();
            clearTimeout(that.slideTimeOut);

            var index = parseInt(getIndex($(this).attr('href')));

            that.gotoItem(index, function() {
                that.setIndex(index);
                that.currentSlide = index;
            });

            return false;
        });
        
        function mouseEnter(){
          clearTimeout(that.slideTimeOut);
        }
      
        function mouseLeave() {
            that.slideTimeOut = setTimeout(function() {
                that.beginSlideshow(that.currentSlide);
            }, 200);
        }      
      
        view.slideContainer.mouseenter(mouseEnter);
        view.indexItems.mouseenter(mouseEnter);
        view.nextLink.mouseenter(mouseEnter);
        view.prevLink.mouseenter(mouseEnter);
      
        view.slideContainer.mouseleave(mouseLeave);
        view.indexItems.mouseleave(mouseLeave);
        view.nextLink.mouseleave(mouseLeave);
        view.prevLink.mouseleave(mouseLeave);      
    }

    SliderController.prototype.gotoItem = function(i, cb) {
        var xpos = i * this.view.slideWidth * -1;

        var that = this;

        if(that.view.transitionType === 'slide'){
            that.view.slideContainer.animate({left : xpos + 'px'}, 'slow', function() {
                if (cb) {
                    cb();
                }
            });
        }

        if(that.view.transitionType === 'fade'){
            that.view.slideContainer.find('li').eq(that.currentSlide).fadeOut('slow', 'swing', function(){
                that.view.slideContainer.find('li').eq(i).fadeIn('slow', 'swing', function(){

                    if (cb) {
                        cb();
                    }
                });
            });


        }
    };

    SliderController.prototype.setIndex = function(i) {
        this.view.indexItems.removeClass('selected');
        $(this.view.indexItems[i]).addClass('selected');
    };

    SliderController.prototype.start = function() {
        var that = this;

        that.beginSlideshow(0);
    };

    SliderController.prototype.beginSlideshow = function(index) {
        var that = this;
        clearTimeout(this.slideTimeOut);

        this.slideTimeOut = setTimeout(function() {
            if(this.slideTimeOut){
                clearTimeout(this.slideTimeOut);
            }
            
            that.setIndex(index);
            that.gotoItem(index, function() {
                that.currentSlide = index;
                var next = that.getNextIndex(index);
                that.beginSlideshow(next);
            });
        }, that.slideInterval);
    };

    SliderController.prototype.getNextIndex = function(i) {
        var next = i == this.slideCount - 1 ? 0 : i + 1;
        return next;
    };

    heroSlider.controller = new SliderController(heroSlider.view, 4000, 'hero carousel');
    heroSlider.controller.start();

    jobsSlider.controller = new SliderController(jobsSlider.view, 2000, 'jobs slider');
    jobsSlider.controller.start();

    // map rollovers
    function changeBackground(val){
        $('.map-container .map').css('background-position', val);
    }

    $('.map-container .americas').bind('mouseover', function(){changeBackground('0 -160px');});
    $('.map-container .emea').bind('mouseover', function(){changeBackground('0 -320px');});
    $('.map-container .asiapac').bind('mouseover', function(){changeBackground('0 -480px');});

    $('.map-container .map-area').bind('mouseout', function(){changeBackground('0 0');});

});
