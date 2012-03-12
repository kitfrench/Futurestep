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
            <?php endif; ?>
        </aside>
          
      </aside>

    <aside class="summaries related-content">
<?php //CHANGEABLE SIDEBAR DEPENDENT ON SIDEBAR CHECKBOXES ?>
<?php if(get_post_meta(get_the_ID(), 'latest-new-events', true)) : ?>
        <div class="summary-group">
          <h1>Latest News and Events</h1>
          <article class="summary">
            <?php
    $args = array( 'post_type' => 'news-events', 'posts_per_page' => 1);
    $loop = new WP_Query( $args );
while ( $loop->have_posts() ) : $loop->the_post(); ?>
                <a  class="title" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                <time datetime=""><?php the_time('d F Y h:j'); ?></time>
<?php endwhile; ?>
                <a class="more-detail" href="#!/news-and-events/register" title="Register for an event" class="more-detail">Register for
              event</a>

                <a  class="more-detail" href="/news-and-events/">View all events</a>
          </article>
        </div>           
<?php endif; ?>
        
<?php if(get_post_meta($post_id, 'find-expert', true)) : ?>
        <div class="summary">
          <h1>Contact your local specialist</h1>

     <form action="/find-an-expert" method="post">
      <label>
        <span>Select a Country</span>
        <select name="country">
          <option value="">Select a country</option>
          <?php 
            $args = array( 'taxonomy' => 'country' );
            $terms = get_terms('country', $args);
            foreach($terms as $term) :  ?>
          <option value="<?php echo $term->name; ?>"><?php echo $term->name; ?></option>
          <?php  endforeach; ?>
        </select>
      </label>
      <label>
        <span>Select a Sector</span>
        <select name="sector">
          <option value="">Select a sector</option>
          <?php 
            $args = array( 'taxonomy' => 'sector' );
            $terms = get_terms('sector', $args);
            foreach($terms as $term) :  ?>
          <option <?php if(is_page($term->slug)) echo 'selected="selected" ';?>value="<?php echo $term->name; ?>"><?php echo $term->name; ?></option>
          <?php  endforeach; ?>
        </select>
      </label>
      <label>
        <span>Select a Solution</span>
        <select name="solution">
          <option value="">Select a solution</option>
          <?php 
            $args = array( 'taxonomy' => 'solution' );
            $terms = get_terms('solution', $args);
            foreach($terms as $term) :  ?>
          <option value="<?php echo $term->name; ?>"><?php echo $term->name; ?></option>
          <?php  endforeach; ?>
        </select>
      </label>
      <input type="submit" name="find-expert" value="Search" class="button"/>
    </form>
        </div>
<?php endif; ?>
        
<?php if(get_post_meta($post_id, 'related-links', true)) : ?>
    <div class="summary">
      <h1>Related Links</h1>
      <?php for ($i = 1; $i <= 3; $i++) : ?>
      <?php if(get_post_meta($post_id, 'link-'.$i.'-title', true)!='') : ?>
      <p>
        <a class="title" href="<?php echo get_post_meta($post_id, 'link-'.$i.'-address', true) ?>"><?php echo get_post_meta($post_id, 'link-'.$i.'-title', true) ?></a>
        <span><?php echo get_post_meta($post_id, 'link-'.$i.'-source', true) ?></span>
      </p>
      <?php endif; ?>
      <?php endfor; ?>     
      
    </div>
<?php endif; ?>
        
    </aside>

</section>
  </div>
<?php get_footer(); ?>
