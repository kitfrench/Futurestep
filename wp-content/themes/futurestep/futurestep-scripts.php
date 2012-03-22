<!-- styles -->
<link rel="shortcut icon" href="/favicon.ico" />
<link href="<?php bloginfo('template_url'); ?>/css/html5reset-1.6.1.css" rel="stylesheet"/>
<link href="<?php bloginfo('template_url'); ?>/css/fonts/webfontkit/stylesheet.css" rel="stylesheet"/>
<link href="<?php bloginfo('template_url'); ?>/css/styleguide.css" rel="stylesheet"/>

<!-- scripts -->
<!--[if lt IE 10]>
<link href="<?php bloginfo('template_url'); ?>/css/ie-styles.css" rel="stylesheet"/>
<script src="//html5shim.googlecode.com/svn/trunk/html5.js" type="text/javascript"></script>
<![endif]-->


<script src="<?php bloginfo('template_url'); ?>/js/libs/jquery.1.6.2.js"></script>


<?php //JS DEPENDENT ON WHICH PAGE - ?>
<script src="<?php bloginfo('template_url'); ?>/js/futurestep-main.js"></script>
<?php if(is_home()) : ?>
<script src="<?php bloginfo('template_url'); ?>/js/futurestep-home.js"></script>
<?php endif; ?>
<?php if(is_page(icl_object_id(207, 'page'))) : ?>
<script src="<?php bloginfo('template_url'); ?>/js/futurestep-events.js"></script>
<?php endif; ?>
<?php if(is_page(icl_object_id(38, 'page'))) : ?>
<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?sensor=true"></script>
<script src="<?php bloginfo('template_url'); ?>/js/office-finder.js"></script>


<script type="text/javascript">
  $(function(){
<?php 
$contactregionsearch = (urldecode($wp_query->query_vars['category-region'])) ? urldecode($wp_query->query_vars['category-region']) : $_POST['category-region'];
//      echo $contactregionsearch;
        if($contactregionsearch=='') : ?>
    var region = 'worldwide'; 
<?php else : ?>
    var region = '<?php echo $contactregionsearch; ?>'; 
<?php endif; ?>

var locations = [
        <?php

        function eout($metaKey, $pid){
            echo(htmlspecialchars(addslashes(get_post_meta($pid, $metaKey, true))));
        };

        $args = array( 'post_type' => 'locations', 'posts_per_page' => 1000);
        $loop = new WP_Query( $args );

        while ( $loop->have_posts() ) :
            $loop->the_post();
            $pid = get_the_ID();
            if( get_post_meta($pid, 'location-latitude', true)!='' &&
                get_post_meta($pid, 'location-longitude', true)!='') :?>
            {
                title: '<?php the_title(); ?>',
                address : ['<span style="font-weight:bold;"><?php echo the_title()?></span>', '<?php eout("office-address-line-1", $pid); ?>', '<?php eout("office-address-line-2", $pid); ?>', '<?php eout("office-address-city", $pid); ?>', '<?php eout("office-address-region", $pid); ?>', '<?php eout("office-address-post-code", $pid); ?>'],
                position : new google.maps.LatLng(<?php eout("location-latitude", $pid); ?>, <?php eout("location-longitude", $pid); ?>)
            }
            <?php if(($loop->current_post + 1) != ($loop->post_count))
                echo ",";

            endif;
        endwhile; ?>
    ];
window.initMap(region, locations);
});
</script>
<?php endif;?>

<?php if(is_singular('experts')) : ?>
<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?sensor=true"></script>
<script src="<?php bloginfo('template_url'); ?>/js/expert-location.js"></script>
<script type="text/javascript">
    $(function(){
        var latlng = new google.maps.LatLng(<?php echo get_post_meta(get_the_ID(), 'expert-latitude', true); ?>, <?php echo get_post_meta(get_the_ID(), 'expert-longitude', true); ?>);
        var valzoom = 17;
        initMap(latlng, valzoom);
    });
</script>
<?php endif; ?>



