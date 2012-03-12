/**
 * Date: 12/08/2011
 * To change this template use File | Settings | File Templates.
 */

if ($) {

    var fs = {};

    fs.view = {
        servicesnav : null,
        ataglancePanel : null,

        init: function() {
            this.servicesnav = $('#services-navigation');
            this.servicesnav.hide();
        },

        toggleServiceNav : function() {
            var that = this;

            function changeArrow() {
                var $servicesEl = $('#services span.decoration');
                var rightArrowState = $servicesEl.hasClass('right-arrow-orange');

                if (rightArrowState) {//set to on
                    $servicesEl.removeClass('right-arrow-orange');
                    $servicesEl.addClass('down-arrow-orange');
                }
                else {//set to off
                    $servicesEl.removeClass('down-arrow-orange');
                    $servicesEl.addClass('right-arrow-orange');
                }
            }

            this.servicesnav.fadeToggle('fast', function() {
                changeArrow();

                if (that.servicesnav.is(':visible')) {
                    $('#services-back-plate').bind("click",
                        function() {
                            that.servicesnav.hide();
                            $('#services-back-plate').hide();
                            changeArrow();
                        }).show();
                }
            });
        }

    };

    fs.Controller = function(view) {

        view.init();

        $('#services').click(function(e) {
            e.preventDefault();
            view.toggleServiceNav();
            return false;
        });

        return {};
    };

    $(function() {

        $('body').append('<div id="services-back-plate" style="background-color:transparent;  position:absolute; top:0; left:0; width:99%; height:100%; z-index:15; display:none"></div>');

        var ctrl = fs.Controller(fs.view);

    });
}
