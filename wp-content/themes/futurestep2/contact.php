<?php /* Template Name: Contact Us */ ?>
<?php get_header(); ?>
<div class="core-content-container">
  <div class="butterflies-tall-pattern">
    <section class="core-content about-us-container">
      <section class="detail">
        <nav class="index">
          <ul>
            <li class="selected"><a href="/contact-us">Global offices</a></li>
            <li class="last-item"><a href="/find-an-expert">Find an Expert</a></li>
          </ul>
        </nav>
        <div id="map-container">

        </div>
        <div class="filter">
          <form action="/contact-us" method="post">
            <label>
              <span style="vertical-align: baseline;" class="filter-label">Filter by Country</span>
              <select name="category-region" class="country-filter">
                <option value="">Show all offices</option>
                <?php 
            $args = array( 'taxonomy' => 'category-region' );
                $terms = get_terms('category-region', $args);
                foreach($terms as $term) : ?>
                <?php if($term->parent==0) : ?>
                <option
                <?php if($_POST['category-region']==$term->name) echo 'selected="selected" ';
                ?>value="<?php echo $term->
                name; ?>"><?php echo $term->name; ?></option><?php endif; ?>
                <?php  endforeach; ?>
              </select>
            </label>

            <input type="submit" name="find-office" value="Search" class="button"/>
          </form>
        </div>
        <section class="contacts">
          <?php if($_POST['category-region']=='') :
    $args = array( 'taxonomy' => 'category-region' );
          $terms = get_terms('category-region', $args);
          foreach($terms as $term) : ?>
          <?php if($term->parent==0) : ?>
          <div title="Office Locations in <?php echo $term->name ?>" class="territory">
            <h1><?php echo $term->name; ?></h1>

            <ul class="experts">
              <?php
            $args = array( 'post_type' => 'locations', 'category-region' => $term->name);
              $loop = new WP_Query( $args );

              while ( $loop->have_posts() ) : $loop->the_post(); ?>
              <li>
                <h3><?php the_title(); ?></h3>

                <div class="vcard">
                  <span class="fn org">Futurestep</span>

                  <p class="adr">
                    <span class="street-address"><?php echo get_post_meta(get_the_ID(), 'office-address-line-1', true) ?></span><br/>

                    <span><?php echo get_post_meta(get_the_ID(), 'office-address-line-2', true) ?></span><br/>
                    <span class="locality"><?php echo get_post_meta(get_the_ID(), 'office-address-city', true) ?></span>,
                    <abbr class="region"><?php echo get_post_meta(get_the_ID(), 'office-address-region', true) ?></abbr>
                    <span class="postal-code"><?php echo get_post_meta(get_the_ID(), 'office-address-post-code', true) ?></span><br/>
                    <span>Phone: <?php echo get_post_meta(get_the_ID(), 'office-address-phone', true) ?></span><br/>
                    <span class="tel"><?php echo get_post_meta(get_the_ID(), 'office-address-phone', true) ?></span>
                  </p>

                </div>
              </li>
              <?php endwhile; ?>
            </ul>
          </div>
          <?php endif; ?>
          <?php endforeach; ?>


          <?php else : ?>

          <div title="Office Locations in <?php echo $_POST['office-region']; ?>" class="territory">
            <h1><?php echo $_POST['category-region']; ?></h1>

            <ul>
              <?php
            $args = array( 'post_type' => 'locations', 'category-region' => $_POST['category-region']);
              $loop = new WP_Query( $args );

              while ( $loop->have_posts() ) : $loop->the_post(); ?>
              <li>
                <h3><?php the_title(); ?></h3>

                <div class="vcard">
                  <span class="fn org">Futurestep</span>

                  <p class="adr">
                    <span class="street-address"><?php echo get_post_meta(get_the_ID(), 'office-address-line-1', true) ?></span><br/>

                    <span><?php echo get_post_meta(get_the_ID(), 'office-address-line-2', true) ?></span><br/>
                    <span class="locality"><?php echo get_post_meta(get_the_ID(), 'office-address-city', true) ?></span>,
                    <abbr class="region"><?php echo get_post_meta(get_the_ID(), 'office-address-region', true) ?></abbr>
                    <span class="postal-code"><?php echo get_post_meta(get_the_ID(), 'office-address-post-code', true) ?></span><br/>
                    <span>Phone: <?php echo get_post_meta(get_the_ID(), 'office-address-phone', true) ?></span><br/>
                    <span class="tel"><?php echo get_post_meta(get_the_ID(), 'office-address-phone', true) ?></span>
                  </p>

                </div>
              </li>
              <?php endwhile; ?>
            </ul>
          </div>
          <?php endif; ?>
        </section>
      </section>
    </section>
  </div>
</div>
<?php get_footer(); ?>
