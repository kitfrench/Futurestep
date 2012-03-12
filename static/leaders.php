<?php /* Template Name: Leadership Team */ ?>
<?php get_header(); ?>
<div class="core-content-container bridge-illustration">
    <section class="core-content about-us-container">
        <section class="detail">
                <nav class="index">
                    <ul>
                          <li class="first-item"><a href="/about-us">About Us</a></li>
                          <li class="selected"><a href="/about-us/leaders">Leadership Team</a></li>
                    </ul>
                </nav>
            
            <section class="body">
                <h1>Leadership Team</h1>
                <ul class="leadership-team">
<?php
            $args = array( 'post_type' => 'leaders','leader-country' => $_POST['country']);
            $loop = new WP_Query( $args );

            while ( $loop->have_posts() ) : $loop->the_post(); ?>
                <li class="member">
                        <?php the_post_thumbnail( array(90,105) ); ?>

                        <p>
                            <a class="title name" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            <span class="position"><?php echo get_post_meta(get_the_ID(), 'leader-position', true) ?></span>
                            <a class="read-more" href="<?php the_permalink(); ?>">Read More</a>

                        </p>
                </li>
              <?php endwhile; ?>
                </ul>
            </section>
            
            <aside class="features">
                <aside class="summary">
                    <h1>Find in Country</h1>

                    <form action="/about-us/leaders" method="post">
                <label>
                    <span>Select a Country</span>
                <select name="country">
                    <option value="">Select a Country</option>
          <?php 
            $args = array( 'taxonomy' => 'leader-country' );
            $terms = get_terms('leader-country', $args);
            foreach($terms as $term) :  ?>
                    <option <?php if($_POST['country']==$term->name) echo 'selected="selected" '; ?>value="<?php echo $term->name; ?>"><?php echo $term->name; ?></option>
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
