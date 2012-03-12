  <aside id="latest-insights" class="summaries">
    <div class="summary-group">
        <h1><?php echo icl_t('Home', 'Latest Insights', 'Latest Insights'); ?></h1>
        <?php
            $args = array( 'post_type' => 'insight', 'posts_per_page' => 1, 'meta_key'=>'featured', 'meta_value'=>'1');
            $loop = new WP_Query( $args );
            while ( $loop->have_posts() ) : $loop->the_post();
                $contributors = get_post_meta(get_the_ID(), 'contributors');
                $contributors = implode(",", $contributors);
            ?>
            <article class="summary">
                <a class="title" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                <p><?php echo $contributors; ?>, 
                    <time datetime="<?php the_time('Y-m-d'); ?>"><?php the_time('M d Y'); ?></time>
                </p>
            </article>
            <?php
            endwhile;
        ?>
        <a href="<?php echo get_permalink(icl_object_id(34, 'page')); ?>" class="more-detail"><?php echo icl_t('Home', 'View all insights', 'View all insights'); ?></a>
    </div>
     <div class="summary-group">
        <?php outputhomejobsslider(); ?>
     </div>
  </aside>