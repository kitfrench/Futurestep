<?php /* Template Name: News Landing Page */ ?>
<?php include('header.php');
global $post;
setup_postdata($post);
?>
<section class="introduction">
    <div class="headline">
  <?php
    $args = array( 'post_type' => array('news-events'), 'posts_per_page' => 1, 'meta_key'=>'landing-featured', 'meta_value'=>'1' );
    $loop = new WP_Query( $args );
    
    while ( $loop->have_posts() ) : $loop->the_post();
    $contributors = get_post_meta(get_the_ID(), 'contributors', true);
?>
    <h1><a href="<?php the_permalink();?>"><?php the_title(); ?></a></h1>
    <p class="source"><?php if($contributors!='') echo $contributors.', '; ?><?php echo date( 'M d Y', $eventdate); ?>&nbsp;<?php echo strftime('%H:%M', $eventtime); ?></p>
    <?php endwhile; ?>
        <a href="<?php echo get_permalink(icl_object_id(207, 'page')); ?>"><?php echo icl_t('News - Landing', 'View events calendar', 'View events calendar'); ?></a>
      
    </div>
</section>

  <div class="core-content-container">
      <section class="core-content">
          
    <aside class="summaries">
        <div class="summary-group">
        <h1><?php echo icl_t('News - Landing', 'In the News', 'In the News'); ?></h1>
      
      <?php
        $args = array( 'post_type' => array('news'), 'posts_per_page' => 2, 'meta_key'=>'landing-featured', 'meta_value'=>'1'  );
        $loop = new WP_Query( $args );
    
        while ( $loop->have_posts() ) : $loop->the_post(); 
      ?>
      <article class="summary">
          <a class="title" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
          <p><time datetime="<?php the_time('Y-m-d'); ?>T<?php the_time('G:i:s'); ?>"><?php the_time('M d Y'); ?></time></p>
      </article>
      <?php endwhile; ?>
        
        <a href="<?php bloginfo('siteurl'); ?>/news" class="more-detail"><?php echo icl_t('News - Landing', 'View all news', 'View all news'); ?></a>
        </div>
    </aside>
      
    <aside class="summaries">
        <div class="summary-group">
      <h1><?php echo icl_t('News - Landing', 'Press releases', 'Press releases'); ?></h1>
      <?php
        $args = array( 'post_type' => array('press'), 'posts_per_page' => 2, 'meta_key'=>'landing-featured', 'meta_value'=>'1'  );
        $loop = new WP_Query( $args );
    
        while ( $loop->have_posts() ) : $loop->the_post(); 
      ?>
      <article class="summary">
          <a class="title" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
          <p><time datetime="<?php the_time('Y-m-d'); ?>T<?php the_time('G:i:s'); ?>"><?php the_time('M d Y'); ?></time></p>
      </article>
      <?php endwhile; ?>

      <a href="<?php echo get_permalink(icl_object_id(204, 'page')); ?>" title="<?php echo icl_t('News - Landing', 'View all press releases', 'View all press releases'); ?>" class="more-detail"><?php echo icl_t('News - Landing', 'View all press releases', 'View all press releases'); ?></a>
        </div>
    </aside>
          <aside class="summaries">
 <?php echo get_template_part('related','signup') ?>
          </aside>
    </section>
  </div>

<?php get_footer(); ?>