  <aside id="latest-insights" class="summaries">
            <div class="summary-group">
                <h1>Latest Insights</h1>
    <?php
    $args = array( 'post_type' => 'insight', 'posts_per_page' => 1, 'meta_key'=>'featured', 'meta_value'=>'1');
$loop = new WP_Query( $args );
while ( $loop->have_posts() ) : $loop->the_post(); 
  $contributors = get_post_meta(get_the_ID(), 'contributors');
  $contributors = implode(",", $contributors);
?>  
    <article class="summary">
                    <a class="title" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>

                    <p><?php echo $contributors; ?>
                        <time datetime="<?php the_time('Y-m-d'); ?>"><?php the_time('d/m/Y'); ?></time>

                    </p>
                </article>
    <?php endwhile; ?>
    <a href="/insights" class="more-detail">View all insights</a>
            </div>
      
                <div class="summary">
                <h1>Job oppurtunities with our clients</h1>

                <p>Futurestep is recruiting regularly for the following sectors: Consumer, Financial, Government, Life
                    Sciences/Healthcare, Industrial and Technology</p>
                <a href="#!/recent-roles/" class="continue">See our most recent roles <span class="decoration sprite right-arrow-grey"><span>&gt;</span></span></a>
            </div>
  </aside>