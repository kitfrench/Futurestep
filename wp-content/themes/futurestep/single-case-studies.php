<?php get_header();
        global $post;
        setup_postdata($post);
        $post_id=$post->ID;
        ?>

<div class="core-content-container ">
    <div class="anchored-background-illustration anchored-bridge">
        <section class="core-content">
            <aside class="detail">

                <section class="body">
                    <h1><?php the_title(); ?></h1>

                    <?php the_content(); ?>
                    <?php if(get_post_meta(get_the_ID(), 'downloadable-file', true)!='') : ?>
                    <a href="<?php echo get_post_meta(get_the_ID(), 'downloadable-file', true) ?>" title="<?php echo icl_t('Download PDF', 'Download PDF', 'Download PDF'); ?>"><?php echo icl_t('Download PDF', 'Download PDF', 'Download PDF'); ?></a>
                    <?php endif; ?>
                </section>

                <aside class="features">
                    <?php if(get_post_meta($post_id, 'title-slot-1', true)!='') : ?>
                    <article class="summary">
                        <h1><?php echo get_post_meta($post_id, 'title-slot-1', true); ?></h1>
                        <?php echo htmlspecialchars_decode (get_post_meta(get_the_ID(), 'content-slot-1', true)); ?>
                    </article>
                    <?php endif; ?>
                    <?php if(get_post_meta($post_id, 'title-slot-2', true)!='') : ?>
                    <article class="summary">
                        <h1><?php echo get_post_meta($post_id, 'title-slot-2', true); ?></h1>
                        <?php echo htmlspecialchars_decode (get_post_meta($post_id, 'content-slot-2', true)); ?>
                    </article>
                    <?php endif;?>

                </aside>
            </aside>

            <aside class="summaries related-content">
                <?php //CHANGEABLE SIDEBAR DEPENDENT ON SIDEBAR CHECKBOXES ?>
                <?php if(get_post_meta(get_the_ID(), 'latest-new-events', true)) : ?>
                <div class="summary-group">
                    <h1><?php echo icl_t('Asides', 'Latest News and Events', 'Latest News and Events'); ?></h1>
                    <article class="summary">
                        <?php
                                $args = array( 'post_type' => 'news-events', 'posts_per_page' => 1);
                                $loop = new WP_Query( $args );
                                while ( $loop->have_posts() ) : $loop->the_post(); ?>
                        <a class="title" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        <time datetime=""><?php the_time('d F Y'); ?></time>
                        <?php endwhile;
                                wp_reset_query(); ?>

                        <a class="more-detail" href="<?php echo get_permalink(icl_object_id(207, 'page')); ?>"><?php echo icl_t('Asides', 'View all events', 'View all events'); ?></a>
                    </article>
                </div>
                <?php endif; ?>

                <?php if(get_post_meta($post_id, 'find-expert', true)) :?>
                <?php get_template_part('related', 'findanexpert'); ?>
                <?php endif; ?>

                <?php if(get_post_meta($post_id, 'related-links', true)) : ?>
                <div class="summary">
                    <h1>Related Links</h1>
                    <?php for ($i = 1; $i <= 3; $i++) : ?>
                    <?php if(get_post_meta($post_id, 'link-'.$i.'-title', true)!='') : ?>
                    <p>
                        <a class="title"
                           href="<?php echo get_post_meta($post_id, 'link-'.$i.'-address', true) ?>"><?php echo get_post_meta($post_id, 'link-'.$i.'-title', true) ?></a>
                        <span><?php echo get_post_meta($post_id, 'link-'.$i.'-source', true) ?></span>
                    </p>
                    <?php endif; ?>
                    <?php endfor; ?>

                </div>
                <?php endif; ?>

            </aside>

        </section>
    </div>
</div>
        <?php get_footer(); ?>

