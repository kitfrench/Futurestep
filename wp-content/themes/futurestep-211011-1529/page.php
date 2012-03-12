<?php get_header();
        global $post;
        setup_postdata($post);
        $post_id=$post->ID;
        ?>
<div class="core-content-container">
    <?php if($post_id == 506): ?>
<div class="anchored-background-illustration anchored-tree">
    <?php endif; ?>
    <section class="core-content">
        <aside class="detail">
                       
<?php $children = get_pages('child_of='.$post->ID);
if( count( $children ) != 0 ) : ?>
            <nav class="index">
                <ul>
                    <li class="selected"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></li>
                    <?php wp_list_pages("title_li=&child_of=".$post_id); ?>
                </ul>
            </nav>
<?php elseif($post->post_parent!=265 && $post->post_parent!=7 && $post->post_parent!=263) : ?>
            <nav class="index">
                <ul>
                    <li><a href="<?php echo get_permalink($post->post_parent); ?>"><?php echo get_the_title($post->post_parent); ?></a></li>
                    <?php wp_list_pages("title_li=&child_of=".$post->post_parent); ?>
                </ul>
            </nav>
<?php endif; ?>


    <section class="body">
        <h1><?php the_title(); ?></h1>

        <?php the_content(); ?>
        <?php if(get_post_meta(get_the_ID(), 'downloadable-file', true)!='') : ?>
        <a href="<?php echo get_post_meta(get_the_ID(), 'downloadable-file', true) ?>" title="Download PDF">Download
            PDF</a>
        <?php endif; ?>

    </section>

    <aside class="features">
        <?php if(get_post_meta($post_id, 'title-slot-1', true)!='' || get_post_meta($post_id, 'content-slot-1', true)!='') : ?>
        <article class="summary only-child">
            <h1><?php echo get_post_meta($post_id, 'title-slot-1', true); ?></h1>
            <?php echo htmlspecialchars_decode (get_post_meta(get_the_ID(), 'content-slot-1', true)); ?>
        </article>
        <?php endif; ?>
        <?php if(get_post_meta($post_id, 'title-slot-2', true)!='' || get_post_meta($post_id, 'content-slot-2', true)!='') : ?>
        <article class="summary">
            <h1><?php echo get_post_meta($post_id, 'title-slot-2', true); ?></h1>
            <?php echo htmlspecialchars_decode (get_post_meta($post_id, 'content-slot-2', true)); ?>
        </article>
        <?php endif; ?>
    </aside>

</aside>
<?php if(get_post_meta($post_id, 'related-links', true) || get_post_meta(get_the_ID(), 'latest-new-events', true) || get_post_meta($post_id, 'find-expert', true)) : ?>
<aside class="summaries related-content">
    <?php get_template_part('related','sidebar') ?>
</aside>
<?php else : ?>
<aside class="empty summaries related-content">&nbsp;</aside>
<?php endif; ?>
        </section>
    </div>
    <?php if($post_id == 506): ?>
        </div>
    <?php endif; ?>
        <?php get_footer(); ?>

