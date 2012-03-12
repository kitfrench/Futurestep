  <aside id="latest-news" class="summaries">
            <div class="summary-group">
                <h1>Latest News and Events</h1>
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
                        <time datetime="<?php the_time('Y-m-d'); ?>"><?php the_time('d/m/Y h:j'); ?></time>
                        , <?php echo $contributors; ?>
                    </p>
                    <a href="#!/news-and-events/register" title="Register for an event" class="more-detail">Register for
                        event</a>

    </article>
    <?php endwhile; ?>
  <?php
    $args = array( 'post_type' => array('news-events'), 'posts_per_page' => 1, 'meta_key'=>'featured', 'meta_value'=>'1' );
    $loop = new WP_Query( $args );
    
    while ( $loop->have_posts() ) : $loop->the_post(); 
      $contributors = get_post_meta(get_the_ID(), 'contributors');
      $contributors = implode(",", $contributors);
?>
    <article class="summary">
                    <a class="title" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>

                    <p>
                        <time datetime="<?php the_time('Y-m-d'); ?>"><?php the_time('d/m/Y h:j'); ?></time>
                        , <?php echo $contributors; ?>
                    </p>
                    <a href="#!/news-and-events/register" title="Register for an event" class="more-detail">Register for
                        event</a>

    </article>
    <?php endwhile; ?>
                
    <a href="/news-and-events" title="View all news and events" class="more-detail">View all news and
                    events</a>
            </div>
      
      <div class="summary">
                <h1>Working for us</h1>

                <p>
                    Futurestep has a global presence, with offices across four continents. No matter which one of them
                    interests you, we encourage you to register and start the application process immediately.
                </p>
                <a href="/working-with-us" class="continue">Find out more<span class="decoration sprite right-arrow-grey"><span>&gt;</span></span></a>
      </div>
    
  </aside>