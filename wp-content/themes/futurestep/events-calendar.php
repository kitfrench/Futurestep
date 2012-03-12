<?php /* Template Name: Events Calendar */
get_header(); 
$date = urldecode($wp_query->query_vars['eventyear']);
if( $date!='') {
    $date= explode('/', $date);
}
    
    $year = ($date=='') ? date("Y") : $date[0];
    $month = (count($date)<2) ? date("m") : $date[1];

?>
<div class="core-content-container butterflies-tall">
<section class="core-content events-calendar-container">
<section class="detail">
    <h1><?php the_title(); ?></h1>
    
    <aside class="calendar">
        <div class="months">
            <a href="<?php echo get_permalink(icl_object_id(207, 'page')); ?>/<?php echo date("Y", mktime(0, 0, 0, $month-1, 1, $year)); ?>/<?php echo date("m", mktime(0, 0, 0, $month-1, 1, $year)); ?>"><span class="hidden">previous</span></a>
            <span class="current-month"><?php echo date("M Y", mktime(0, 0, 0, $month, 1, $year)); ?></span>
            <a href="<?php echo get_permalink(icl_object_id(207, 'page')); ?>/<?php echo date("Y", mktime(0, 0, 0, $month+1, 1, $year)); ?>/<?php echo date("m", mktime(0, 0, 0, $month+1, 1, $year)); ?>" class="next"><span class="hidden">next</span></a>
        </div>
        <table>
            <thead class="days-of-week">
            <tr>
                <td title="Sunday"><?php echo substr(icl_t('Events Calendar', 'Saturday', 'Saturday'), 0, 1); ?><span class="hidden"><?php echo substr(icl_t('Events Calendar', 'Saturday', 'Saturday'), 1); ?></span></td>
                <td title="Monday"><?php echo substr(icl_t('Events Calendar', 'Monday', 'Monday'), 0, 1); ?><span class="hidden"><?php echo substr(icl_t('Events Calendar', 'Monday', 'Monday'), 1); ?></span></td>
                <td title="Tuesday"><?php echo substr(icl_t('Events Calendar', 'Tuesday', 'Tuesday'), 0, 1); ?><span class="hidden"><?php echo substr(icl_t('Events Calendar', 'Tuesday', 'Tuesday'), 1); ?></span></td>
                <td title="Wednesday"><?php echo substr(icl_t('Events Calendar', 'Wednesday', 'Wednesday'), 0, 1); ?><span class="hidden"><?php echo substr(icl_t('Events Calendar', 'Tuesday', 'Tuesday'), 1); ?></span></td>
                <td title="Thursday"><?php echo substr(icl_t('Events Calendar', 'Thursday', 'Thursday'), 0, 1); ?><span class="hidden"><?php echo substr(icl_t('Events Calendar', 'Thursday', 'Thursday'), 1); ?></span></td>
                <td title="Friday"><?php echo substr(icl_t('Events Calendar', 'Friday', 'Friday'), 0, 1); ?><span class="hidden"><?php echo substr(icl_t('Events Calendar', 'Friday', 'Friday'), 1); ?></span></td>
                <td title="Saturday"><?php echo substr(icl_t('Events Calendar', 'Saturday', 'Saturday'), 0, 1); ?><span class="hidden"><?php echo substr(icl_t('Events Calendar', 'Saturday', 'Saturday'), 1); ?></span></td>
            </tr>
            </thead>
        
        <tbody class="days-in-month">
            <tr>
<?php
$rowcount = 1;
$num = (date("N", mktime(0, 0, 0, $month, 1, $year))==7) ? 0 : date("N", mktime(0, 0, 0, $month, 1, $year));
    for ($i = 1; $i <= $num; $i++) {
        ?>
        <td><a title="empty" href="">
            <time datetime="">&nbsp;<span class="hidden">&nbsp;</span></time>
        </a></td>
<?php 
$rowcount++;
} ?>
<?php
$num = cal_days_in_month(CAL_GREGORIAN, $month, $year);
    for ($i = 1; $i <= $num; $i++) {
        $args = array( 'post_type'=>'news-events', 'year'=>$year, 'monthnum'=>$month , 'day'=>$i);
        $loop = new WP_Query( $args );
        if(is_int(($rowcount-1)/7)) echo "</tr><tr>\n";
            $rowcount++;
        ?>
       <td<?php if (dateHasPosts($wpdb, $year, $month, $i, 'news-events') > 0) echo ' class="has-events"'; ?>>

                <a title="<?php echo date("d-M-Y", mktime(0, 0, 0, $month, $i, $year)); ?>" href="<?php echo get_permalink(icl_object_id(207, 'page')); ?>/<?php echo date("Y", mktime(0, 0, 0, $month, $i, $year)); ?>/<?php echo date("m", mktime(0, 0, 0, $month, $i, $year)); ?>/<?php echo date("d", mktime(0, 0, 0, $month, $i, $year)); ?>">
            <time datetime="<?php echo date("d-m-Y", mktime(0, 0, 0, $month, $i, $year)); ?>T<?php echo date("G:j:s", mktime(0, 0, 0, $month, $i, $year)); ?>"><?php echo $i; ?><span class="hidden"><?php echo date("M Y", mktime(0, 0, 0, $month, $i, $year)); ?></span></time>
       </a></td>
        <?php
    }
?>        
<?php
$num = (date("N", mktime(0, 0, 0, $month, $i-1, $year))==7) ? 0 : date("N", mktime(0, 0, 0, $month, $i-1, $year));
//echo $num;
    for ($i=6; $i > $num; $i--) {
        ?>
        <td><a title="empty" href="">
            <time datetime="">&nbsp;<span class="hidden">&nbsp;</span></time>
        </a></td>
<?php } ?>
            </tr>
        </tbody>
    </table>
    </aside>

<aside class="events-for-month">
     <ul class="chronological">
<?php
  $qrydate = ($date[2]!='') ? $year."-".$month."-".$date[2] : $year."-".$month."-%" ;
  $querystr = "
    SELECT $wpdb->posts.* 
    FROM $wpdb->posts, $wpdb->postmeta
    WHERE $wpdb->posts.ID = $wpdb->postmeta.post_id 
    AND $wpdb->postmeta.meta_key = 'events-date' 
    AND $wpdb->postmeta.meta_value LIKE '".$qrydate."' 
    AND $wpdb->posts.post_status = 'publish' 
    AND $wpdb->posts.post_type = 'news-events'
    AND $wpdb->posts.post_date < NOW()
    ORDER BY $wpdb->postmeta.meta_value ASC
  ";
  
 $pageposts = $wpdb->get_results($querystr, OBJECT);
                        if ( $pageposts ) : 

 foreach ($pageposts as $post): 
$contributors = get_post_meta(get_the_ID(), 'contributors');
$contributors = implode(",", $contributors);
$eventdate = strtotime(get_post_meta(get_the_ID(), 'events-date', true));
$eventtime = strtotime(get_post_meta(get_the_ID(), 'events-time', true));
?>
    <li>
        <time datetime="<?php echo date( 'y-m-d', $eventdate ); ?>T00:00:00"><?php echo date( 'M', $eventdate ); ?> <span class="day-of-month"><?php echo date( 'd', $eventdate ); ?></span> <span class="hidden"><?php echo date( 'Y', $eventdate ); ?></span>
        </time>
            <p class="event-summary">
                <a class="title" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>

                <span class="time-location-hosts">
                    <time datetime="<?php echo date( 'y-m-d', $eventdate ); ?>T00:00:00"><?php echo date( 'd/m/Y', $eventdate ); ?> <?php echo date( 'h:i', $eventtime ); ?></time>, <?php echo $contributors; ?>
                </span>
            </p>
            
        <div class="controls">
                <a href="#!show-hide" class="expand" style="display:none"><span
                        class="hidden">show / hide details</span></a>
                <a href="#!show-hide" class="contract" style="display:block"><span
                        class="hidden">show / hide details</span></a>
        </div>
        
        <div class="event-detail">
            <?php echo format_content($post->post_content); ?>
            <?php $eventdate = strtotime(get_post_meta(get_the_ID(), 'events-date', true)); ?>
            <a class="button" href="mailto:marketing@futurestep.com?Subject=Register%20for%20event%20<?php echo date( 'd/m/Y', $eventdate ); ?>,%20<?php the_title(); ?>">Register for event</a>
        </div>

    </li>

				<?php endforeach; ?>
                        <?php else : ?>
    <?php if($date[2]!='') : ?>
    <li>
        <h2 class="noevent"><?php echo icl_t('Events Calendar', 'No events to show', 'We have no events scheduled this month. Use the links below to search for forthcoming events'); ?></h2>
    </li>
    <?php else : ?>
    <li>
        <h2 class="noevent"><?php echo icl_t('Events Calendar', 'No events to show', 'We have no events scheduled this month. Use the links below to search for forthcoming events'); ?></h2>
    </li>
    <?php endif; ?>
			<?php endif; ?>
     </ul>
<?php
 $x=1;
 $forwardslide = false;
 while($forwardslide == false && $x<12) {
        $slidemonth=$month+$x;
        $qrytime  = mktime(0, 0, 0, $slidemonth  , 1, $year);
        $qrydate = date("Y-m-%", $qrytime);
        $querystr = "
            SELECT $wpdb->posts.* 
            FROM $wpdb->posts, $wpdb->postmeta
            WHERE $wpdb->posts.ID = $wpdb->postmeta.post_id 
            AND $wpdb->postmeta.meta_key = 'events-date' 
            AND $wpdb->postmeta.meta_value LIKE '".$qrydate."' 
            AND $wpdb->posts.post_status = 'publish' 
            AND $wpdb->posts.post_type = 'news-events'
            AND $wpdb->posts.post_date < NOW()
            ORDER BY $wpdb->posts.post_date DESC
            ";
  
        $forwardslide = $wpdb->get_results($querystr, OBJECT);
        $x++;
        $foremonth = date("m", $qrytime);
        $foreyear = date("Y", $qrytime);
    } 

$y=1;
$rearslide = false;
 while($rearslide == false && $y<12) {
        $slidemonth=$month-$y;
        $qrytime  = mktime(0, 0, 0, $slidemonth  , 1, $year);
        $qrydate = date("Y-m-%", $qrytime);
        $querystr = "
            SELECT $wpdb->posts.* 
            FROM $wpdb->posts, $wpdb->postmeta
            WHERE $wpdb->posts.ID = $wpdb->postmeta.post_id 
            AND $wpdb->postmeta.meta_key = 'events-date' 
            AND $wpdb->postmeta.meta_value LIKE '".$qrydate."' 
            AND $wpdb->posts.post_status = 'publish' 
            AND $wpdb->posts.post_type = 'news-events'
            AND $wpdb->posts.post_date < NOW()
            ORDER BY $wpdb->posts.post_date DESC
            ";

        $rearslide = $wpdb->get_results($querystr, OBJECT);
        $y++;
        $rearmonth = date("m", $qrytime);
        $rearyear = date("Y", $qrytime);
    } 
    ?>
    <div class="alt-dates">
        <?php if($x<12) : ?>
            <a class="right" href="<?php echo get_permalink(icl_object_id(207, 'page')); ?>/<?php echo date("Y", mktime(0, 0, 0, $foremonth, 1, $foreyear)); ?>/<?php echo date("m", mktime(0, 0, 0, $foremonth, 1, $foreyear)); ?>"><?php echo icl_t('Events Calendar', 'Forthcoming Events', 'Forthcoming Events'); ?></a>
        <?php endif;
        if($y<12) :?>
            <a href="<?php echo get_permalink(icl_object_id(207, 'page')); ?>/<?php echo date("Y", mktime(0, 0, 0, $rearmonth, 1, $rearyear)); ?>/<?php echo date("m", mktime(0, 0, 0, $rearmonth, 1, $rearyear)); ?>"><?php echo icl_t('Events Calendar', 'Previous Events', 'Previous Events'); ?></a>
        <?php endif; ?>
    </div>
    
</aside>

</section>
</section>
</div>

<?php get_footer(); ?>