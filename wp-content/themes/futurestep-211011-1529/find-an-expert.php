<?php /* Template Name: Find an Expert */ ?>
<?php get_header(); ?>
<div class="core-content-container bridge-illustration">
    <section class="core-content about-us-container">
        <section class="detail">
            <nav class="index experts-index">

                <ul>
                    <li><a href="/contact-us">Global offices</a></li>
                    <li class="selected last-item"><a href="/find-an-expert">Find an Expert</a>
                    </li>
                </ul>
            </nav>
            <section class="body">

                <ul class="experts">
<?php
            $args = array( 'post_type' => 'experts', 'category-sector' => $_POST['sector'], 'category-region' => $_POST['country'], 'category-solution' => $_POST['service']);
            $loop = new WP_Query( $args );

            while ( $loop->have_posts() ) : $loop->the_post(); ?>
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
                    <h1>Find an Expert</h1>

                    <form action="<?php bloginfo('siteurl'); ?>/find-an-expert" method="post">
                    <label>
                        <span>Select a Country</span>
                        <select name="country">
                            <option value="">Select a country</option>
          <?php 
            $args = array( 'taxonomy' => 'category-region' );
            $terms = get_terms('category-region', $args);
            foreach($terms as $term) : ?>
                            <?php if($term->parent != 0) :?><option <?php if($_POST['country']==$term->name) echo 'selected="selected" '; ?>value="<?php echo $term->name; ?>"><?php echo $term->name; ?></option><?php endif; ?>
          <?php  endforeach; ?>
                        </select>
                    </label>
                    <label>
                        <span>Select a Sector</span>
                        <select name="sector">
                            <option value="">Select a sector</option>
          <?php 
            $args = array( 'taxonomy' => 'category-sector' );
            $terms = get_terms('category-sector', $args);
            foreach($terms as $term) :  ?>
                            <option <?php if($_POST['sector']==$term->name) echo 'selected="selected" '; ?>value="<?php echo $term->name; ?>"><?php echo $term->name; ?></option>
          <?php  endforeach; ?>
                        </select>
                    </label>
                    <label>
                        <span>Select a Solution</span>
                        <select name="solution">
                            <option value="">Select a solution</option>
          <?php 
            $args = array( 'taxonomy' => 'category-solution' );
            $terms = get_terms('category-solution', $args);
            foreach($terms as $term) :  ?>
                            <?php if($term->parent != 0) :?><option value="<?php echo $term->name; ?>"><?php echo "-".$term->name; ?></option><?php else : ?>
                            <option <?php if($_POST['solution']==$term->name) echo 'selected="selected" '; ?>value="<?php echo $term->name; ?>"><?php echo $term->name; ?></option><?php endif; ?>
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
