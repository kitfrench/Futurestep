$(function() {
    //TODO: implement javascript for:
    // changing the filter
    // selecting an address.
    //http://code.google.com/apis/maps/documentation/javascript/services.html#Geocoding

    var latlng = new google.maps.LatLng(34.0522342, -118.2436849);

    var myOptions = {
        zoom: 17,
        center: latlng,
        mapTypeId: google.maps.MapTypeId.ROADMAP
    };
    
    var map = new google.maps.Map(document.getElementById("map-container-expert"),
        myOptions);
});