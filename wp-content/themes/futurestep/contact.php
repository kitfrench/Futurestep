<?php /* Template Name: Contact Us */
        $contactregionsearch = (urldecode($wp_query->query_vars['category-region'])) ? urldecode($wp_query->query_vars['category-region']) : $_POST['category-region'];
        ?>
<?php get_header(); ?>
<div class="core-content-container">
    <div class="butterflies-tall-pattern">
        <section class="core-content about-us-container">
        <section class="detail">
        <nav class="index">
            <ul>
                <li class="selected"><a
                        href="<?php echo get_permalink(icl_object_id(38, 'page')); ?>"><?php echo icl_t('Contact', 'Global offices', 'Global offices'); ?></a>
                </li>
                <li class="last-item"><a
                        href="<?php echo get_permalink(icl_object_id(303, 'page')); ?>"><?php echo icl_t('Contact', 'Find an Expert', 'Find an Expert'); ?></a>
                </li>
            </ul>
        </nav>
        <div id="map-container">

        </div>
        <div class="filter">
            <form action="<?php echo get_permalink(icl_object_id(38, 'page')); ?>" method="post">
                <label>
                    <span style="vertical-align: baseline;"
                          class="filter-label"><?php echo icl_t('Contact', 'Filter by Region', 'Filter by Region'); ?></span>
                    <select name="category-region" class="country-filter">
                        <option value=""><?php echo icl_t('Contact', 'Show all offices', 'Show all offices'); ?></option>
                        <?php
                                $args = array( 'taxonomy' => 'category-region' );
                                $terms = get_terms('category-region', $args);
                                foreach($terms as $term) : ?>
                        <?php if($term->parent==0 && $term->slug!='global') : ?>
                        <option
                        <?php if($contactregionsearch==$term->slug) echo 'selected="selected" ';
                                ?>value="<?php echo $term->slug; ?>"><?php echo $term->name; ?></option><?php endif; ?>
                    <?php  endforeach; ?>
                </select>
            </label>

            <input type="submit" name="find-office" value="<?php echo icl_t('Search button', 'Search', 'Search'); ?>"
                   class="button"/>
        </form>
    </div>
    <section class="contacts">
        <?php if($contactregionsearch=='') :
                $args = array( 'taxonomy' => 'category-region', 'posts_per_page' => 1000);
                $terms = get_terms('category-region', $args);
                foreach($terms as $term) : ?>
        <?php if($term->parent==0 && $term->slug!='global') : ?>
        <div title="Office Locations in <?php echo $term->name ?>" class="territory">
            <h1><?php echo $term->name; ?></h1>

            <ul class="experts">
                <?php
                        $args = array( 'post_type' => 'locations', 'category-region' => $term->slug, 'orderby' => 'title', 'order' => 'ASC', 'nopaging'=> true);
                        $loop = new WP_Query( $args );

                        while ( $loop->have_posts() ) : $loop->the_post(); ?>
                <li>
                    <h3><?php the_title(); ?></h3>

                    <div class="vcard">
                        <span class="fn org">Futurestep</span>

                        <p class="adr">
                            <?php echo surroundStringWith(get_post_meta(get_the_ID(), 'office-address-line-1', true), '<span class="street-address">{{replace}}</span><br/>') ?>
                            <?php echo surroundStringWith(get_post_meta(get_the_ID(), 'office-address-line-2', true), '<span>{{replace}}</span><br/>') ?>
                            <?php echo surroundStringWith(get_post_meta(get_the_ID(), 'office-address-city', true), '<span class="locality">{{replace}}</span><br/>') ?>
                            <?php echo surroundStringWith(get_post_meta(get_the_ID(), 'office-address-region', true), '<abbr class="region">{{replace}}</abbr><br/>') ?>
                            <?php echo surroundStringWith(get_post_meta(get_the_ID(), 'office-address-post-code', true), '<span class="postal-code">{{replace}}</span><br/>') ?>
                            <?php echo surroundStringWith(get_post_meta(get_the_ID(), 'office-address-phone', true), '<span>Phone: <span class="tel">{{replace}}</span></span><br/>') ?>
                        </p>
                    </div>
                </li>
                <?php endwhile; ?>
            </ul>
        </div>
        <?php endif; ?>
        <?php endforeach; ?>


        <?php else : ?>
        <?php $termdetails = get_term_by('slug', $contactregionsearch, 'category-region'); ?>
        <div title="Office Locations in <?php echo $contactregionsearch; ?>" class="territory">
            <h1><?php echo $termdetails->name; ?></h1>

            <ul>
                <?php
                        $args = array( 'post_type' => 'locations', 'category-region' => $contactregionsearch, 'orderby' => 'title', 'order' => 'ASC', 'nopaging'=> true, 'posts_per_page' => 1000);
                        $loop = new WP_Query( $args );

                        while ( $loop->have_posts() ) : $loop->the_post(); ?>
                <li>
                    <h3><?php the_title(); ?></h3>

                    <div class="vcard">
                        <span class="fn org">Futurestep</span>

                        <p class="adr">
                            <span class="street-address"><?php echo getAddressLine('office-address-line-1') ?></span><br/>
                            <span><?php echo getAddressLine('office-address-line-2') ?></span><br/>
                            <span class="locality"><?php echo getAddressLine('office-address-city') ?></span>,
                            <abbr class="region"><?php echo getAddressLine('office-address-region') ?></abbr>
                            <span class="postal-code"><?php echo getAddressLine('office-address-post-code') ?></span><br/>
                            <span>Phone: <span class="tel"><?php echo getAddressLine('office-address-phone') ?></span></span>
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
