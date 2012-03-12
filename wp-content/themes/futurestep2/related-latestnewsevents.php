<div class="summary-group">
    <h1>Latest News and Events</h1>
    <article class="summary">
        <?php
                $args = array( 'post_type' => 'news-events', 'posts_per_page' => 1);
                $loop = new WP_Query( $args );
                while ( $loop->have_posts() ) : $loop->the_post(); ?>
        <a class="title" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
        <time datetime=""><?php the_time('d F Y h:j'); ?></time>
        <?php endwhile; ?>
        <a class="more-detail" href="mailto:marketing@futurestep.com?Subject=Please%20register%20me%20for%20the%20newsletter" title="Register for an event" class="more-detail">Register
            for event</a>
        <a class="more-detail" href="/news-and-events/">View all events</a>
    </article>
</div>