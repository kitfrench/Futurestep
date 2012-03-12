<?php /* Template Name: Leadership Team */ ?>
<?php get_header();
global $post;
setup_postdata($post);
?>
<div class="core-content-container bridge-illustration">
    <section class="core-content about-us-container">
        <section class="detail">
                <nav class="index">
                    <ul>
                        <li><a href="<?php echo get_permalink($post->post_parent); ?>"><?php echo get_the_title($post->post_parent); ?></a></li>
                        <?php wp_list_pages("title_li=&child_of=".$post->post_parent); ?>
                    </ul>
                </nav>
            
            <section class="body">
                <h1>Leadership Team</h1>
                <div class="intro"><?php the_content(); ?></div>
                <ul class="leadership-team">
<?php
  $querystr = "
    SELECT $wpdb->posts.* 
    FROM $wpdb->posts, $wpdb->postmeta
    WHERE $wpdb->posts.ID = $wpdb->postmeta.post_id 
    AND (($wpdb->postmeta.meta_key = 'global-leader' AND $wpdb->postmeta.meta_value = '1') 
    OR ($wpdb->postmeta.meta_key = 'regional-leader' AND $wpdb->postmeta.meta_value = '1'))
    AND $wpdb->posts.post_status = 'publish' 
    AND $wpdb->posts.post_type = 'experts'
    AND $wpdb->posts.post_date < NOW()
    ORDER BY $wpdb->posts.menu_order ASC, $wpdb->posts.post_title ASC
  ";
  
 $pageposts = $wpdb->get_results($querystr, OBJECT);
                        if ( $pageposts ) : 

 foreach ($pageposts as $post):
                            $tax_args=array("fields" => "names");
                            $tags = wp_get_post_terms( $post->ID , 'category-region', $tax_args);
                if (in_array($_POST['country'], $tags) || $_POST['country']==''):?>
                <li class="member">
                        <?php the_post_thumbnail( array(90,105) ); ?>
                        <p>
                            <a class="title name" href="<?php echo $siteurl.'/leaders/'.$post->post_name."?l"; ?>"><?php the_title(); ?></a>
                            <span class="position"><?php echo get_post_meta(get_the_ID(), 'expert-position', true) ?></span>
                            <a class="read-more" href="<?php echo $siteurl.'/leaders/'.$post->post_name."?l"; ?>">Read More</a>
                        </p>
                </li>
              <?php endif; endforeach; endif; ?>
                </ul>
            </section>
            
            <aside class="features">
                <aside class="summary only-child">
                    <h1>Regional leadership</h1>

                    <form action="/about-us/leaders" method="post">
                <label>
                    <span>Select a Country</span>
                <select name="country">
                    <option value="">Select a Country</option>
          <?php 
            $args = array( 'taxonomy' => 'category-region' );
            $terms = get_terms('category-region', $args);
            foreach($terms as $term) :  ?>
                    <?php if($term->parent==0) : ?><option <?php if($_POST['country']==$term->name) echo 'selected="selected" '; ?>value="<?php echo $term->name; ?>"><?php echo $term->name; ?></option><?php endif; ?>
          <?php  endforeach; ?>
                </select>
                </label>
                <input type="submit" name="find-expert" value="Search" class="button"/>
                   </form>
                </aside>
            </aside>
                    

    </section>
    </section>
</div>
<?php get_footer(); ?>
