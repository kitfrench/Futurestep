<?php /* Template Name: Find an Expert */ ?>
<?php get_header(); ?>
<div class="core-content-container bridge-illustration">
    <section class="core-content about-us-container">
        <section class="detail">
            <nav class="index experts-index">
                <ul>
                    <li><a href="<?php echo get_permalink(icl_object_id(38, 'page')); ?>"><?php echo icl_t('Contact', 'Global offices', 'Global offices'); ?></a></li>
                    <li class="selected last-item"><a href="<?php echo get_permalink(icl_object_id(303, 'page')); ?>"><?php echo icl_t('Contact', 'Find an Expert', 'Find an Expert'); ?></a>
                    </li>
                </ul>
            </nav>
            <section class="body">
                <ul class="experts">
                <?php
					$args = array();
					$args['post_type'] = 'experts';
					$args['posts_per_page'] = 1000;
					$args['orderby'] = 'title';
					$args['order'] = 'ASC';
					 
					if (@$_POST['sector'] > ' ')
					 	$args['category-sector'] = $_POST['sector'];
					if (@$_POST['country'] > ' ')
					 	$args['category-region'] = $_POST['country'];
					if (@$_POST['service'] > ' ')
					 	$args['category-solution'] = $_POST['service'];					 
						
                    $loop = new WP_Query( $args );

                  while ( $loop->have_posts() ) : $loop->the_post();
                ?>
                    <li class="member">
                        <?php the_post_thumbnail( array(90,105) ); ?>
                        <p>
                            <a class="title name" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            <span class="position"><?php echo get_post_meta($post->ID, 'expert-position', true); ?></span>
                            <a class="read-more" href="<?php the_permalink(); ?>">Read More</a>
                        </p>
                    </li>
                <?php endwhile; ?>
                </ul>
            </section>
            
            <aside class="features">
                <aside class="summary">
                    <h1><?php echo icl_t('Job Form', 'Find an expert', 'Find an expert'); ?></h1> 

                <form action="<?php echo get_permalink(icl_object_id(303, 'page')); ?>" method="post">
                    <label>
                        <span><?php echo icl_t('Job Form', 'Select a country', 'Select a country'); ?></span>
                        <select name="country">
                            <option value=""><?php echo icl_t('Job Form', 'Select a country', 'Select a country'); ?></option>
          <?php 
            $args = array( 'taxonomy' => 'category-region' );
            $terms = get_terms('category-region', $args);
            foreach($terms as $term) : ?>
                            <?php if($term->parent != 0) :?><option <?php if($_POST['country']==$term->slug) echo 'selected="selected" '; ?>value="<?php echo $term->slug; ?>"<?php if($term->name==ICL_LANGUAGE_NAME && $_POST['country']=='') echo ' selected="selected"'; ?>><?php echo $term->name; ?></option><?php endif; ?>
          <?php  endforeach; ?>
                        </select>
                    </label>
                    <label>
                        <span><?php echo icl_t('Job Form', 'Select a sector', 'Select a sector'); ?></span>
                        <select name="sector">
                            <option value=""><?php echo icl_t('Job Form', 'Select a sector', 'Select a sector'); ?></option>
          <?php 
            $args = array( 'taxonomy' => 'category-sector' );
            $terms = get_terms('category-sector', $args);
            foreach($terms as $term) :  ?>
                            <option <?php if($_POST['sector']==$term->slug) echo 'selected="selected" '; ?>value="<?php echo $term->slug; ?>"><?php echo $term->name; ?></option>
          <?php  endforeach; ?>
                        </select>
                    </label>
                    <!--<p class="note">OR</p>-->
                    <label>
                        <span><?php echo icl_t('Job Form', 'Select a solution', 'Select a solution'); ?></span>
                        <select name="service">
                            <option value=""><?php echo icl_t('Job Form', 'Select a solution', 'Select a solution'); ?></option>
          <?php 
            $args = array( 'taxonomy' => 'category-solution', 'hide_empty' => false, 'parent' => 0 );
            $terms = get_terms('category-solution', $args);
            $x=0;
            foreach($terms as $term) : ?>
                            <option <?php if($_POST['service']==$term->slug) echo 'selected="selected" '; ?>value="<?php echo $term->slug; ?>"><?php echo $term->name; ?></option>
            <?php $args = array( 'taxonomy' => 'category-solution', 'hide_empty' => false, 'parent' => $term->term_id );
                $terms2 = get_terms('category-solution', $args);
                $x=0;
                foreach($terms2 as $term2) : ?>
                            <option <?php if($_POST['service']==$term2->slug) echo 'selected="selected" '; ?>value="<?php echo $term2->slug; ?>"><?php echo "-".$term2->name; ?></option>
                <?php  endforeach; ?>
            <?php  endforeach; ?>
                        </select>
                    </label>
                      <input type="submit" name="find-expert" value="<?php echo icl_t('Search button', 'Search', 'Search'); ?>" class="button"/>
                </form>
                </aside>
            </aside>
        </section>
    </section>
</div>
<?php get_footer(); ?>
