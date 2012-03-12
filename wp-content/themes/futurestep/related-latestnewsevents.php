<div class="summary-group">
    <h1><?php echo icl_t('Asides', 'Latest News and Events', 'Latest News and Events'); ?></h1>
    <article class="summary">
        <?php
                $newsargs = array( 'post_type' => 'news-events', 'posts_per_page' => 1);
                $newsloop = new WP_Query( $newsargs );
                while ( $newsloop->have_posts() ) : $newsloop->the_post(); ?>
        <a class="title" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
        <time datetime=""><?php the_time('d F Y'); ?></time>
        <?php endwhile; ?>
        <a class="more-detail" href="<?php echo get_permalink(icl_object_id(207, 'page')); ?>"><?php echo icl_t('Asides', 'View all events', 'View all events'); ?></a>
    </article>
</div>