<?php get_header(); ?>
<?php 
global $post;
setup_postdata($post);
?>
    <div class="core-content-container bridge-illustration">
        <section class="core-content about-us-container">
            <section class="detail">
                <?php if(is_page('about-us')) : ?>
                <nav class="index">
                    <ul>
                          <li class="selected"><a href="/about-us">About Us</a></li>
                          <li class="last-item"><a href="/about-us/leaders">Leadership Team</a></li>
                    </ul>
                </nav>
                <?php endif; ?>
                <section class="body">
                    <h1><?php the_title(); ?></h1>

                    <?php the_content(); ?>

                </section>
                
                <aside class="features">
                    <article class="summary">
                        <?php if(get_post_meta(get_the_ID(), 'content-slot-1', true)) : ?>
                        <blockquote><?php echo strip_tags(htmlspecialchars_decode(get_post_meta(get_the_ID(), 'content-slot-1', true))); ?>
                        </blockquote>
                        <p>
                            <?php echo get_post_meta(get_the_ID(), 'title-slot-1', true); ?>
                        </p>
                        <?php endif; ?>
                    </article>

                </aside>
            </section>
        </section>
    </div>
<?php get_footer(); ?>
