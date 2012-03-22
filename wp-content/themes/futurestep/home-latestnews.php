  <aside id="latest-news" class="summaries">
            <div class="summary-group">
                <h1><?php echo icl_t('Home', 'Latest News and Events', 'Latest News and Events'); ?></h1>
  <?php
    $args = array( 'post_type' => array('news'), 'posts_per_page' => 1, 'meta_key'=>'featured', 'meta_value'=>'1' );
    $loop = new WP_Query( $args );
    
    while ( $loop->have_posts() ) : $loop->the_post(); 
      $contributors = get_post_meta(get_the_ID(), 'contributors');
      $contributors = implode(",", $contributors);
?>
    <article class="summary">
                    <a class="title" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>

                    <p>
                        <time datetime="<?php the_time('Y-m-d'); ?>"><?php the_time('M d Y'); ?></time>
                        <?php if($contributors!='') echo ', '.$contributors; ?>
                    </p>

    </article>
    <?php endwhile; ?>
  <?php
    $args = array( 'post_type' => array('news-events'), 'posts_per_page' => 1, 'meta_key'=>'featured', 'meta_value'=>'1' );
    $loop = new WP_Query( $args );
    
    while ( $loop->have_posts() ) : $loop->the_post(); 
      $contributors = get_post_meta(get_the_ID(), 'contributors');
      $contributors = implode(",", $contributors);
      $eventdate = strtotime(get_post_meta(get_the_ID(), 'events-date', true));
      $eventtime = strtotime(get_post_meta(get_the_ID(), 'events-time', true));
?>
    <article class="summary">
                    <a class="title" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>

                    <p>
                        <time datetime="<?php echo date( 'Y-m-d', $eventdate ); ?>"><?php echo date( 'M d Y', $eventdate ); ?></time>
                        <?php if($contributors!='') echo ', '.$contributors; ?>
                    </p>

                    <a href="mailto:marketing@futurestep.com?Subject=Register%20for%20event%20<?php echo date( 'M d Y', $eventdate ); ?>,%20<?php the_title(); ?>"><?php echo icl_t('Home', 'Register for event', 'Register for event'); ?></a>

    </article>
    <?php endwhile; ?>
                
    <a href="<?php echo get_permalink(icl_object_id(36, 'page')); ?>" title="<?php echo icl_t('Home', 'View all news and events', 'View all news and events'); ?>" class="more-detail"><?php echo icl_t('Home', 'View all news and events', 'View all news and events'); ?></a>
            </div>
      
      <div class="summary">
                <h1><?php echo icl_t('Home', 'Working for us', 'Working for us'); ?></h1>

                <p>
                    <?php echo icl_t('Home', 'Working for us - prose', 'Futurestep has a global presence, with offices across five continents. No matter which one of them
                    interests you, we encourage you to register and start the application process immediately.'); ?>
                </p>

                <a href="<?php echo get_permalink(icl_object_id(3949, 'page')); ?>" class="continue" target="new"><?php echo icl_t('Home', 'Find out more', 'Find out more'); ?><span class="decoration sprite right-arrow-grey"><span>&gt;</span></span></a>
      </div>
    
  </aside>