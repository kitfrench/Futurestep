$(function() {
    //TODO: implement javascript for:
    // changing the filter
    // selecting an address.
    //http://code.google.com/apis/maps/documentation/javascript/services.html#Geocoding

    function initMap(region, locations) {

        var maps = {

            'worldwide' : {
                zoom: 1,
                center: new google.maps.LatLng(-34.397, 150.644),
                mapTypeId: google.maps.MapTypeId.SATELLITE
            },

            'americas' : {
                zoom: 3,
                center: new google.maps.LatLng(54.5259614, -105.2551187),
                mapTypeId: google.maps.MapTypeId.SATELLITE
            },

            'europe' : {
                zoom: 3,
                center: new google.maps.LatLng(54.5259614, 15.2551187),
                mapTypeId: google.maps.MapTypeId.SATELLITE
            },

            'asiapac' : {
                zoom: 3,
                center: new google.maps.LatLng(3.1624555302378474, 120.234375),
                mapTypeId: google.maps.MapTypeId.SATELLITE
            }
        };

        var map = new google.maps.Map(document.getElementById("map-container"), maps[region]);

        function addMarker(location) {

            location.marker = new google.maps.Marker({
                map: map,
                position: location.position,
                icon: './css/img/fs-map-marker.png',
                title : location.title
            });
        }

        for (var i = 0; i < locations.length; i++) {
            addMarker(locations[i]);
        }

    }

    window.initMap = initMap;
    //-- -- example :


    var locations = [
            {
                title: 'Atlanta',
                address : '1201 W. Peachtree Strett N.W., Suite 2500, Atlanta, GA 30309',
                position : new google.maps.LatLng(33.7875243, -84.3877779)
            },
            {
                title: 'Dallas',
                address : '1201 W. Peachtree Strett N.W., Suite 2500, Atlanta, GA 30309',
                position : new google.maps.LatLng(32.9612754, -96.83860240000001)
            },
            {
                title: 'Stamford',
                address : '201 Broad Street, Stamford, CT, 06901',
                position : new google.maps.LatLng(41.0553071, -73.53373920000001)
            },
            {
                title: 'Houston',
                address : '2929 Allen Parkway, Suite 3400, Houston, TX 77091',
                position : new google.maps.LatLng(29.7616233, -95.3967624)
            },
            {
                title: 'Los Angeles',
                address : '1900 Avenue of the Stars Suite 2600 Los Angeles, CA 90067',
                position : new google.maps.LatLng(34.0598779, -118.416832)
            },
            {
                title: 'Washington DC',
                address : '1700 K Street NW Suite 700 Washington, DC 20006',
                position : new google.maps.LatLng(38.9022446, -77.03990620000002)
            },
            {
                title: 'Atlanta',
                address : '1201 W. Peachtree Strett N.W., Suite 2500, Atlanta, GA 30309',
                position : new google.maps.LatLng(33.7875243, -84.3877779)
            },
            {
                title: 'San Francisco',
                address : '1201 W. Peachtree Strett N.W., Suite 2500, Atlanta, GA 30309',
                position : new google.maps.LatLng(33.7875243, -84.3877779)
            },
            {
                title: 'Calgary',
                address : '1201 W. Peachtree Strett N.W., Suite 2500, Atlanta, GA 30309',
                position : new google.maps.LatLng(33.7875243, -84.3877779)
            },
            {
                title: 'Montreal',
                address : '1201 W. Peachtree Strett N.W., Suite 2500, Atlanta, GA 30309',
                position : new google.maps.LatLng(33.7875243, -84.3877779)
            },
            {
                title: 'Vancouver',
                address : '1201 W. Peachtree Strett N.W., Suite 2500, Atlanta, GA 30309',
                position : new google.maps.LatLng(33.7875243, -84.3877779)
            },
            {
                title: 'Toronto',
                address : '1201 W. Peachtree Strett N.W., Suite 2500, Atlanta, GA 30309',
                position : new google.maps.LatLng(33.7875243, -84.3877779)
            },
            {
                title: 'Brussels',
                address : '1201 W. Peachtree Strett N.W., Suite 2500, Atlanta, GA 30309',
                position : new google.maps.LatLng(33.7875243, -84.3877779)
            }

        ];

    initMap('worldwide', locations);



});