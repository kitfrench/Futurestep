<?php /* Template Name: Sitemap */ ?>
<?php get_header(); ?>
<?php 
global $post;
setup_postdata($post);
?>
    <div class="core-content-container bridge-illustration">
        <section class="core-content about-us-container sitemap">
            <section class="detail">
               <h2 id="posts">Site Map</h2>
               <h3>Pages</h3>
                  <ul class="pagelist">
                      <?php wp_list_pages('title_li='); ?>
                  </ul>

<?php 
$post_types=get_post_types('','objects'); 
foreach ($post_types as $post_type ) :
if($post_type->name != 'post' && $post_type->name != 'page' && $post_type->name != 'attachment' && $post_type->name != 'revision' && $post_type->name != 'nav_menu_item') :
echo '<h3>'.$post_type->labels->name.'</h3>'
?>
                <ul class="pagelist">  
<?php 
$args = array( 'post_type' => $post_type->name);
$loop = new WP_Query( $args );
  while ( $loop->have_posts() ) : $loop->the_post(); ?>
                  <li><a href="<?php the_permalink(); ?>" ><?php echo the_title(); ?></a></li>
<?php endwhile; ?>
                </ul>
<?php 
endif;
endforeach; ?>
            </section>
        </section>
    </div>
<?php get_footer(); ?>
