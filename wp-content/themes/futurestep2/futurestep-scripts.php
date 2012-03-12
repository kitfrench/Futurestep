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
<?php if(is_page('events-calendar')) : ?>
<script src="<?php bloginfo('template_url'); ?>/js/futurestep-events.js"></script>
<?php endif; ?>
<?php if(is_page('contact-us')) : ?>
<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?sensor=true"></script>
<script src="<?php bloginfo('template_url'); ?>/js/office-finder.js"></script>

<script type="text/javascript">
  $(function(){
<?php if($_POST['category-region']=='') : ?>
    var region = 'worldwide'; // <-- should be set via php var
<?php else : ?>
    var region = '<?php echo $_POST['category-region']; ?>'; // <-- should be set via php var
<?php endif; ?>

var locations = [
        <?php
        $args = array( 'post_type' => 'locations');
        $loop = new WP_Query( $args );

        while ( $loop->have_posts() ) : $loop->the_post();
            if( get_post_meta(get_the_ID(), 'location-latitude', true)!='' &&
                get_post_meta(get_the_ID(), 'location-longitude', true)!='') :?>
            {
                title: '<?php the_title(); ?>',
                address : '<?php echo get_post_meta(get_the_ID(), "office-address-line-1", true) ?>, <?php echo get_post_meta(get_the_ID(), "office-address-line-2", true) ?>, <?php echo get_post_meta(get_the_ID(), "office-address-city", true) ?>, <?php echo get_post_meta(get_the_ID(), "office-address-region", true) ?>, <?php echo get_post_meta(get_the_ID(), 'office-address-post-code', true) ?>',
                position : new google.maps.LatLng(<?php echo get_post_meta(get_the_ID(), 'location-latitude', true); ?>, <?php echo get_post_meta(get_the_ID(), 'location-longitude', true); ?>)
            }
            <?php if(($loop->current_post + 1) != ($loop->post_count))
                echo ",";

            endif;
        endwhile; ?>
    ];
initMap(region, locations);
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
<script type="text/javascript" language="javascript" src="http://tracker.leadforensics.com/js/2538.js" ></script>
