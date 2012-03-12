
<?php
  // Include WordPress
  define('WP_USE_THEMES', false);
  require('/home/rinntech/public_html/futurestep/wp-blog-header.php');
  ?>

$(function() {
    //TODO: implement javascript for:
    // changing the filter
    // selecting an address.
    //http://code.google.com/apis/maps/documentation/javascript/services.html#Geocoding

    var latlng = new google.maps.LatLng(-34.397, 150.644);

    var myOptions = {
        zoom: 1,
        center: latlng,
        mapTypeId: google.maps.MapTypeId.SATELLITE
    };

    var map = new google.maps.Map(document.getElementById("map-container"),
        myOptions);



    var locations = [
    <?php
            $args = array( 'post_type' => 'locations', 'office-region' => $term->name);
            $loop = new WP_Query( $args );

            while ( $loop->have_posts() ) : $loop->the_post(); 
            if(get_post_meta(get_the_ID(), 'location-latitude', true)!='' && get_post_meta(get_the_ID(), 'location-longitude', true)!='') :
            ?>
        {
            title: '<?php the_title(); ?>',
            address : '1201 W. Peachtree Strett N.W., Suite 2500, Atlanta, GA 30309',
            position : new google.maps.LatLng(<?php echo get_post_meta(get_the_ID(), 'location-latitude', true); ?>, <?php echo get_post_meta(get_the_ID(), 'location-longitude', true); ?>)
        },
<?php 
endif;
endwhile; ?>

    ];

    function addMarker(location){

        location.marker = new google.maps.Marker({
            map: map,
            position: location.position
        });
    }

    for(var i = 0; i < locations.length; i++){
        addMarker(locations[i]);
    }

});