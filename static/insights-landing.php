<?php /* Template Name: Insights Landing Page */ ?>
<?php $contentclass='cityscape-illustration';?>
<?php include('header.php');
global $post;
setup_postdata($post);
?>

<section class="introduction">
    <div class="headline">
  <?php
    $args = array( 'post_type' => array('insight'), 'posts_per_page' => 1, 'meta_key'=>'top-featured', 'meta_value'=>'1' );
    $loop = new WP_Query( $args );
    
    while ( $loop->have_posts() ) : $loop->the_post();
        $contributors = get_post_meta(get_the_ID(), 'contributors');
        $contributors = implode(",", $contributors);
?>
    <h1><a href="<?php the_permalink();?>" title="<?php the_title(); ?>"><?php the_title(); ?></a></h1>
    <p class="source"><?php echo $contributors; ?>, <?php the_time('d/m/Y'); ?></p>
    
    <?php endwhile; ?>
    
      <div class="read-more">
        <?php if(get_post_meta(get_the_ID(), 'downloadable-file', true)!='') : ?>
        <a href="<?php echo get_post_meta(get_the_ID(), 'downloadable-file', true) ?>" title="Download PDF">Download PDF</a>
        <?php endif; ?>
        <a href="/insight">View all insights</a>
      </div>
    </div>
  </section>


<div class="core-content-container">
    <section class="core-content"> 
    <aside class="summaries">
    
          <div class="summary-group">
            <h1>Featured Opinion</h1>
      <?php
    $args = array( 'post_type' => array('opinion'), 'posts_per_page' => 2, 'meta_key'=>'insight-featured-opinions', 'meta_value'=>'1' );
    $loop = new WP_Query( $args );
    
    while ( $loop->have_posts() ) : $loop->the_post();
        $opinionsource = get_post_meta(get_the_ID(), 'opinion-source', TRUE);
?>    <article class="summary">
                    <a class="title" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                    <p><?php echo $opinionsource; ?>
                        <time datetime="<?php the_time('Y-m-d'); ?>"><?php the_time('d/m/Y'); ?></time>
                    </p>
      </article>
<?php endwhile; ?>
            <a href="/opinion" class="more-detail">View all opinions</a>
          </div>
    </aside>
      
    <aside class="summaries">
        <div class="summary-group">
            <h1>Featured Blog Post</h1>
<?php
    $args = array( 'post_type' => array('insight'), 'posts_per_page' => 2, 'meta_key'=>'middle-featured', 'meta_value'=>'1' );
    $loop = new WP_Query( $args );
    
    while ( $loop->have_posts() ) : $loop->the_post();
        $contributors = get_post_meta(get_the_ID(), 'contributors');
        $contributors = implode(",", $contributors);
?>
            <article class="summary">
                <a class="title" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                <p class="source">Posted by: <?php echo $contributors; ?></p>
            </article>
<?php endwhile; ?>
            <a href="/insight" title="View all new and events" class="more-detail">View all posts</a>
        </div>
    </aside>
          
    <aside class="summaries">
        <div class="summary-group">
            <h1>Most Read Articles</h1>
      <?php
    $args = array( 'post_type' => array('articles'), 'posts_per_page' => 3, 'meta_key'=>'most-read', 'meta_value'=>'1' );
    $loop = new WP_Query( $args );
    
    while ( $loop->have_posts() ) : $loop->the_post();
        $subtitle = get_post_meta(get_the_ID(), 'contributors', true);
?>
                <article class="summary">
                    <a class="title" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                    <p><?php echo $subtitle; ?></p>
                </article>
<?php endwhile; ?>
            <a href="/articles" title="View all new and events" class="more-detail">See all articles</a>
         </div>
    </aside>
        
    </section>
</div>

<?php get_footer(); ?>