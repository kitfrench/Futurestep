<?php /* Template Name: Press Releases */
$year = (urldecode($wp_query->query_vars['release'])=='') ? date('Y') : urldecode($wp_query->query_vars['release']);
?>
<?php get_header(); ?>
    <div class="core-content-container">
        <section class="core-content">
            <section class="detail">

                <section class="press-releases">
                    <h1>Press Releases</h1>
                        <ul class="year-index">
                             <li<?php if($year==2011) echo ' class="current"'; ?>><a href="/news-and-events/press-releases/2011/">2011</a></li>
                             <li<?php if($year==2010) echo ' class="current"'; ?>><a href="/news-and-events/press-releases/2010/">2010</a></li>
                             <li<?php if($year==2009) echo ' class="current"'; ?>><a href="/news-and-events/press-releases/2009/">2009</a></li>
                                <li<?php if($year==2008) echo ' class="current"'; ?>><a href="/news-and-events/press-releases/2008/">2008</a></li>
                        </ul>
            <!-- -->
<?php
            $args = array( 'post_type' => 'press', 'year' => $year);
            $loop = new WP_Query( $args );
            if($loop->have_posts()) : ?>
                        <ul class="chronological">
            <?php
            while ( $loop->have_posts() ) : $loop->the_post(); ?>
                            <li>
                                <time datetime="<?php the_time('d-m-Y'); ?>T00:00:00"><?php the_time('M'); ?> <span class="day-of-month"><?php the_time('d'); ?></span>
                                    <span class="hidden"><?php the_time('Y'); ?></span>
                                </time>

                                <p>
                                <a class="title" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                           <?php if(get_post_meta(get_the_ID(), 'downloadable-file', true)!='') : ?>
                                <a href="<?php echo get_post_meta(get_the_ID(), 'downloadable-file', true) ?>" title="Download PDF">Download PDF</a>
                        <?php endif; ?>
                                </p>
                            </li>
              <?php endwhile; ?>
                        </ul>
            <?php else : ?>
                    <h2>There are no press releases in this year.</h2>
            <?php endif; ?>

                </section>
            </section>
        <?php get_template_part('related','signup') ?>
    </section>
    </div>
<?php get_footer(); ?>