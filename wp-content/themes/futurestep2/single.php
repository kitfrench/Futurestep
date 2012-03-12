<?php /* Template Name: Solutions - Bridge */ ?>
<?php get_header();  
global $post;
setup_postdata($post);
$post_id=get_the_ID();
?>
  <div class="core-content-container bridge-illustration">
    <section class="core-content">
      <aside class="detail">

        <section class="body">
            <h1><?php the_title(); ?></h1>

            <?php the_content(); ?>
            
            <?php if(get_post_meta(get_the_ID(), 'downloadable-file', true)!='') : ?>
               <a href="<?php echo get_post_meta(get_the_ID(), 'downloadable-file', true) ?>" title="Download PDF">Download PDF</a>
            <?php endif; ?>

        </section>
          
        <aside class="features">
            <?php if(get_post_meta($post_id, 'title-slot-1', true)!='' || get_post_meta($post_id, 'content-slot-1', true)!='') : ?>
            <article class="summary">
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
            <?php if(is_singular('news-events')) : 
            $eventdate = strtotime(get_post_meta(get_the_ID(), 'events-date', true)); ?>
            <div class="register-button">
            <a class="button" href="mailto:marketing@futurestep.com?Subject=Register%20for%20event%20<?php echo date( 'd/m/Y', $eventdate ); ?>,%20<?php the_title(); ?>">Register for event</a>
            </div>
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
<?php get_footer(); ?>
