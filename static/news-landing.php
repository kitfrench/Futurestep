<?php /* Template Name: News Landing Page */ ?>
<?php $contentclass='chess-illustration';?>
<?php include('header.php');
global $post;
setup_postdata($post);
?>
<section class="introduction">
    <div class="headline">
  <?php
    $args = array( 'post_type' => array('news-events'), 'posts_per_page' => 1, 'meta_key'=>'featured', 'meta_value'=>'1' );
    $loop = new WP_Query( $args );
    
    while ( $loop->have_posts() ) : $loop->the_post();
    $contributors = get_post_meta(get_the_ID(), 'contributors', true);
?>
    <h1><?php the_title(); ?></h1>
    <p class="source"><?php echo $contributors; ?>, <?php the_time('d/m/Y'); ?></p> 
    <?php endwhile; ?>
        <a href="/news-and-events/events-calendar">View event calendar</a>
      
    </div>
</section>

  <div class="core-content-container">
      <section class="core-content">
          
    <aside class="summaries">
        <div class="summary-group">
        <h1>In the News</h1>
      
      <?php
        $args = array( 'post_type' => array('news'), 'posts_per_page' => 2 );
        $loop = new WP_Query( $args );
    
        while ( $loop->have_posts() ) : $loop->the_post(); 
      ?>
      <article class="summary">
          <a class="title" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
          <p><time datetime="<?php the_time('Y-m-d'); ?>T<?php the_time('h:j:s'); ?>"><?php the_time('d M Y h:j'); ?></time></p>
      </article>
      <?php endwhile; ?>
        
        <a href="/news" class="more-detail">View all news</a>
        </div>
    </aside>
      
    <aside class="summaries">
        <div class="summary-group">
      <h1>Press releases</h1>
      <?php
        $args = array( 'post_type' => array('press'), 'posts_per_page' => 2 );
        $loop = new WP_Query( $args );
    
        while ( $loop->have_posts() ) : $loop->the_post(); 
      ?>
      <article class="summary">
          <a class="title" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
          <p><time datetime="<?php the_time('Y-m-d'); ?>T<?php the_time('h:j:s'); ?>"><?php the_time('d M Y h:j'); ?></time></p>
      </article>
      <?php endwhile; ?>

      <a href="news-and-events/press-releases" title="View all press releases" class="more-detail">View all press releases</a>
        </div>
    </aside>
          
    <aside class="sign-up summaries">
      <div class="summary-group">
          <h1>Sign up to hear more</h1>

          <p>Receive updates on white papers, articles, and other valuable thought leadership resources covering all
            facets of talent acquisition and management.</p>
          <a href="#!/news-and-events/register" title="View all new and events" class="button">Sign up</a>
      </div>
    </aside>
          
    </section>
  </div>

<?php get_footer(); ?>