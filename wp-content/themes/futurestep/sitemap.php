<?php /* Template Name: Sitemap */ ?>
<?php get_header(); ?>
<?php 
global $post;
setup_postdata($post);
?>
    <div class="core-content-container bridge-illustration">
        <section class="core-content about-us-container sitemap">
            <section class="detail">
                <h2 id="posts"><?php the_title(); ?></h2>
                
                <h3><a href="/"><?php icl_t('Site Map', 'Home', 'Home'); ?></a></h3>
                <ul class="pagelist">
                    <li class="level1"><strong><?php echo icl_t('Site Map', 'Services', 'Services'); ?></strong>
                        <ul class="pagelist">
                            <li>
                                <ul class="pagelist">
                                    <?php wp_list_pages(array('child_of' => icl_object_id(7, 'page'), 'title_li' => '<strong>Reruitment</strong>')); ?>
                                </ul>
                            </li>
                            <li>
                                <ul class="pagelist">
                                    <?php wp_list_pages(array('child_of' => icl_object_id(263, 'page'), 'title_li' => '<strong>Consulting</strong>')); ?>
                                </ul>
                            </li>
                            <li>
                                <ul class="pagelist">
                                <?php wp_list_pages(array('child_of' => icl_object_id(265, 'page'), 'title_li' => '<strong>By Sector</strong>')); ?>
                                </ul>
                            </li>
                        </ul>
                    </li>
                    <li class="level1"><a href="<?php echo get_permalink(icl_object_id(32, 'page')); ?>" title="<?php echo icl_t('Site Map', 'Clients', 'Clients'); ?>"><?php echo icl_t('Site Map', 'Clients', 'Clients'); ?></a>
                        <!--
                        Consumer
                        Financial Services
                        Goventment & Not for Profit
                        Industrial
                        Life Sciences
                        Technology
                        -->
                        <ul>
                        <?php
                            $args = array( 'taxonomy' => 'category-sector');
                            $terms = get_terms('category-sector', $args);
                            foreach($terms as $term) :
                        ?>
                            <li>
                                <a href="<?php echo get_permalink(icl_object_id(32, 'page')).'/'.$term->slug; ?>"><?php echo $term->name;?></a>
                            </li>
                        <?php  endforeach; ?>
                        </ul>
                    </li>
                    <li class="level1"><a href="<?php echo get_permalink(icl_object_id(34, 'page')) ?>" title="<?php echo icl_t('Site Map', 'Insights', 'Insights'); ?>"><?php echo icl_t('Site Map', 'Insights', 'Insights'); ?></a>
                        <!--
                        All Insights
                        All Posts
                        All Articles
                        -->
                        <ul>
                            <li><a href="<?php bloginfo('siteurl'); ?>/insight"><?php echo icl_t('Site Map', 'All Insights', 'All Insights'); ?></a></li>
                            <li><a href="<?php bloginfo('siteurl'); ?>/articles"><?php echo icl_t('Site Map', 'All Articles', 'All Articles'); ?></a></li>
                        </ul>
                    </li>
                    <li class="level1"><a href="<?php echo get_permalink(icl_object_id(36, 'page')); ?>" title="<?php echo icl_t('Site Map', 'News and Events', 'News and Events'); ?>"><?php echo icl_t('Site Map', 'News and Events', 'News and Events'); ?></a>
                        <!--
                        Event Calendar
                        All news
                        Press Releases
                        -->
                        <ul>
                            <li><a href="<?php echo get_permalink(icl_object_id(207, 'page')); ?>"><?php echo icl_t('Site Map', 'Event Calendar', 'Event Calendar'); ?></a></li>
                            <li><a href="<?php bloginfo('siteurl'); ?>/news"><?php echo icl_t('Site Map', 'All News', 'All News'); ?></a></li>
                            <li><a href="<?php echo get_permalink(icl_object_id(204, 'page')); ?>"><?php echo icl_t('Site Map', 'Press Release', 'Press Release'); ?></a></li>
                        </ul>
                    </li>
                    <li class="level1"><a href="<?php echo get_permalink(icl_object_id(506, 'page')); ?>" title="<?php echo icl_t('Site Map', 'About Us', 'About Us'); ?>"><?php echo icl_t('Site Map', 'About Us', 'About Us'); ?></a>
                        <!--
                        Leadership Team
                        -->
                        <ul>
                            <li><a href="<?php echo get_permalink(icl_object_id(516, 'page')); ?>"><?php echo icl_t('Site Map', 'Leadership Team', 'Leadership Team'); ?></a></li>
                        </ul>
                    </li>
                    <li class="level1"><a href="<?php echo get_permalink(icl_object_id(38, 'page')); ?>" title="<?php echo icl_t('Site Map', 'Contact', 'Contact'); ?>"><?php echo icl_t('Site Map', 'Contact', 'Contact'); ?></a>
                        <!--
                        APAC  [page of offices in APAC]
                        EMEA [page of offices in EMEA]
                        LATAM [page of offices in LATAM]
                        N.America [page of offices in North America]
                        Find an Expert
                        -->
                        <ul>
                        <?php 
                            $args = array ('taxonomy' => 'category-region');
                            $terms = get_terms('category-region', $args);
                            foreach ($terms as $term):
                        ?>
                            <?php if ($term->parent == 0 && $term->slug != 'global'): ?>
                            <li><a href="<?php echo get_permalink(icl_object_id(38, 'page')); ?>/region/<?php echo $term->slug; ?>"><?php echo $term->name; ?></a></li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                            <li><a href="<?php echo get_permalink(icl_object_id(303, 'page')); ?>"><?php echo icl_t('Site Map', 'Find an Expert', 'Find an Expert'); ?></a></li>
                        </ul>
                    </li>
                    <li class="level1"><a href="<?php echo get_permalink(icl_object_id(503, 'page')); ?>" title="<?php echo icl_t('Site Map', 'Privacy', 'Privacy'); ?>"><?php echo icl_t('Site Map', 'Privacy', 'Privacy'); ?></a></li>
                    <li class="level1"><a href="/contact-us/terms-and-conditions" title="Terms of Use">Terms of Use</a></li>                    
                </ul>
                
                <?php /*
                <h2 id="posts">Site Map</h2>
                <h3>Pages</h3>
                <ul class="pagelist">
                   
                    
                    <li class="page_item page-item-32"><a href="/clients" title="Clients">Clients</a></li>
                    
                    
                    <li class="page_item page-item-34"><a href="/insights" title="Insights">Insights</a></li>
                    
                    
                    <li class="page_item page-item-36"><a href="/news-and-events" title="News and Events">News and Events</a>
                        <ul class='children'>
                            <li class="page_item page-item-207"><a href="/news-and-events/events-calendar" title="Events Calendar">Events Calendar</a></li>
                            <li class="page_item page-item-204"><a href="/news-and-events/press-releases" title="Press Releases">Press Releases</a></li>
                        </ul>
                    </li>
                    <li class="page_item page-item-506"><a href="/about-us" title="About Us">About Us</a>
                        <ul class='children'>
                            <li class="page_item page-item-516"><a href="/about-us/leaders" title="Leadership Team">Leadership Team</a></li>
                        </ul>
                    </li>
                    <li class="page_item page-item-38 current_page_ancestor current_page_parent"><a href="/contact-us" title="Contact">Contact</a>
                        <ul class='children'>
                            <li class="page_item page-item-503"><a href="/contact-us/privacy" title="Privacy">Privacy</a></li>
                            <li class="page_item page-item-498 current_page_item"><a href="/contact-us/sitemap" title="Sitemap">Sitemap</a></li>
                            <li class="page_item page-item-501"><a href="/contact-us/terms-and-conditions" title="Terms of Use">Terms of Use</a></li>
                        </ul>
                    </li>
                    <li class="page_item page-item-303"><a href="/find-an-expert" title="Find an Expert">Find an Expert</a></li>
                </ul> */ ?>              
                                
                <?php /*  
                    //$post_type = array('page');
                    $post_types = get_post_types('','objects');
                                        
                    foreach ($post_types as $post_type ):
                        if (
                          $post_type->name != 'post' && 
                          $post_type->name != 'page' &&
                          $post_type->name != 'attachment' &&
                          $post_type->name != 'revision'):
                ?>
                <h3><?php echo $post_type->labels->name ?></h3>
                <ul class="pagelist">
                <?php
                    $args = array('post_type' => $post_type->name);
                    $loop = new WP_Query ($args);
                    while ($loop->have_posts()): $loop->the_post();
                ?>
                    <li><a href="< ?php the_permalink(); ?>"><?php echo the_title(); ?></a></li>
                <?php endwhile; ?>
                </ul>
                <?php endif; ?>
                <?php endforeach; */ ?>
            </section>
        </section>
    </div>
<?php get_footer(); ?>
