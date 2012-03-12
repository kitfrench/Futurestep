$(function() {
    function initMap(region, locations) {
        var maps = {

            'worldwide' : {
                zoom: 1,
                center: new google.maps.LatLng(-34.397, 150.644),
                mapTypeId: google.maps.MapTypeId.SATELLITE
            },

            'nam' : {
                zoom: 3,
                center: new google.maps.LatLng(54.5259614, -105.2551187),
                mapTypeId: google.maps.MapTypeId.SATELLITE
            },

            'emea' : {
                zoom: 3,
                center: new google.maps.LatLng(54.5259614, 15.2551187),
                mapTypeId: google.maps.MapTypeId.SATELLITE
            },

            'apac' : {
                zoom: 3,
                center: new google.maps.LatLng(3.1624555302378474, 120.234375),
                mapTypeId: google.maps.MapTypeId.SATELLITE
            },

            'americas' : {
                zoom: 3,
                center: new google.maps.LatLng(-26.51488090968832, -56.76821273125),
                mapTypeId: google.maps.MapTypeId.SATELLITE
            }
        };

        function setDefaults(mapname) {
            map = maps[mapname];
            map.panControl = true;
            map.zoomControl = true;
            map.mapTypeControl = false;
            map.scaleControl = true;
            map.streetViewControl = false;
            map.overviewMapControl = false;
            map.scrollwheel = false;
        }

        for (m in maps) {
            setDefaults(m);
        }

        var map = new google.maps.Map(document.getElementById("map-container"), maps[region]);

        function addMarker(location, closeAllFn) {

            location.marker = new google.maps.Marker({
                map: map,
                position: location.position,
                icon: '/wp-content/themes/futurestep/css/img/fs-map-marker.png',
                title : location.title
            });

            function formatAddress(parts) {
                //var parts = unescape(addr).split(',');
                var html = '<br/>'+parts.join('<br/>');

                return html;
            }

            location.infowindow = new google.maps.InfoWindow({content: formatAddress(location.address)});

            google.maps.event.addListener(location.marker, 'click', function() {
                closeAllFn();
                location.infowindow.open(map, location.marker);
            });

        }

        function closeAll(){
            for (var i = 0; i < locations.length; i++) {
                locations[i].infowindow.close();
            }
        }

        for (var i = 0; i < locations.length; i++) {
            addMarker(locations[i], closeAll);
        }

    }

    window.initMap = initMap;
});