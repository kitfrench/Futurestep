<?php /* Template Name: Clients */ ?>
<?php $contentclass='cityscape-illustration';?>
<?php include('header.php');
        global $post;
        setup_postdata($post);
        $sector = urldecode($wp_query->query_vars['sector']);
        $section = '/clients/';
?>
<section class="introduction">
  <div class="headline">
    <?php the_content(); ?>
  </div>
</section>

<div class="core-content-container">
  <section class="core-content">
    <section class="overview">
      <nav class="index">
        <ul>
          <li<?php if($sector=='') echo ' class="selected" ';?>>
            <a href="<?php echo get_bloginfo('siteurl').$section;?>">All Sectors</a></li>
          <?php
                    $args = array( 'taxonomy' => 'category-sector' );
          $terms = get_terms('category-sector', $args);
          foreach($terms as $term) : ?>
          <li
          <?php if($sector==$term->slug) echo ' class="selected" ';?>><a
                href="<?php echo get_bloginfo('siteurl').$section.$term->slug; ?>"><?php echo $term->name;
          ?></a>
          </li>
          <?php  endforeach; ?>
        </ul>
      </nav>
      <h1>Some of our Clients</h1>
      <ul class="client-list">

        <?php
            $args = array( 'post_type' => 'case-studies', 'posts_per_page' => 1000, 'category-sector' => $sector);
        $loop = new WP_Query( $args );
        while ( $loop->have_posts() ) : $loop->the_post();
        $hasCaseStudy = getTruthy(get_post_meta($post->ID, 'has-case-study', true));
        $poppableCls = $hasCaseStudy ? ' class="poppable"' : '';
        ?>
        <li
        <?= $poppableCls?>>
        <div>
         <?php if($hasCaseStudy){?><a class="client-logo" title="<?= $post->post_name ?>" href="<?php the_permalink(); ?>"><?php }?>
          <?php
            echo get_the_post_thumbnail($post->ID, 'fullsize');

          if($hasCaseStudy){?>
          <span class="link-text" title="<?php the_title(); ?>">Read the case study</span>
          <?php }?>
        <?php if($hasCaseStudy){?></a><?php }?>
        </div>
        </li>
        <?php endwhile; ?>
      </ul>
    </section>
  </section>
</div>
<?php get_footer(); ?>
