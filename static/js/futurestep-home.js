$(function() {

    var heroSlider = {};

    heroSlider.view = (function() {

        return {

            slides : $('#carousel ul.headlines li'),
            slideContainer : $('#carousel ul.headlines'),
            indexItems  : $('#carousel ul.index li'),
            indexLinks  : $('#carousel ul.index li a'),
            slideWidth : $($('#carousel ul.headlines li')[0]).outerWidth(),
            transitionType : 'slide'
        };

    })();

    var jobsSlider = {};
    jobsSlider.view = (function() {

        return {
            slides : $('.jobs-slider ul.jobs li'),
            slideContainer : $('.jobs-slider ul.jobs'),
            indexItems  : $('.jobs-slider ul.index li'),
            indexLinks  : $('.jobs-slider ul.index li a'),
            slideWidth : $($('.jobs-slider ul.jobs li')[0]).outerWidth(),
            transitionType : 'fade'
        };

    })();

    function SliderController(view, delay, title) {

        this.title = title;
        this.view = view;
        this.slideCount = view.slides.length;
        this.currentSlide = -1;
        this.slideTimeOut = null;
        this.slideInterval = delay;

        function getIndex(attr) {
            var parts = attr.split('/');

            return parseInt(parts[3]);//Hardcoded - will fail if the href changes
        }

        var that = this;

        view.indexLinks.click(function(e) {
            e.preventDefault();
            clearTimeout(that.slideTimeOut);

            var index = getIndex($(this).attr('href'));

            that.gotoItem(index, function() {
                that.setIndex(index);
                that.currentSlide = index;
            });

            return false;
        });

        view.slideContainer.mouseenter(function() {
            clearTimeout(that.slideTimeOut);
        });

        view.slideContainer.mouseleave(function() {
            setTimeout(function() {
                that.autoPlay(that.currentSlide);
            }, 100)
        });
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

        that.autoPlay(0);
    };

    SliderController.prototype.autoPlay = function(index) {
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
                that.autoPlay(next);
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
});
