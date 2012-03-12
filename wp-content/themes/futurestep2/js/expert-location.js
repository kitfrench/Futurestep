$(function() {
    //TODO: implement javascript for:
    // changing the filter
    // selecting an address.
    //http://code.google.com/apis/maps/documentation/javascript/services.html#Geocoding

    function initMap(latlng, valzoom) {

    var myOptions = {
        zoom: valzoom,
        center: latlng,
        mapTypeId: google.maps.MapTypeId.ROADMAP
    };
    
    var map = new google.maps.Map(document.getElementById("map-container-expert"),
        myOptions);
    }
    
    window.initMap = initMap;
});