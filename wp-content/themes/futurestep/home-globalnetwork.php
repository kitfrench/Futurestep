<aside id="global-network" class="summaries">
            <div class="summary">

                <h1><?php echo icl_t('Home', 'Global Network', 'Global Network'); ?></h1>
                <a class="title" href="<?php echo get_permalink(icl_object_id(38, 'page')); ?>"><?php echo icl_t('Home', '39 offices in 20 countries with 800+ professionals', '39 offices in 20 countries with 800+ professionals'); ?></a>
                <div class="map-container">
                    <div class="map">
                        <!--<img src="<?php bloginfo('template_url'); ?>/img/dummyimg_worldmap.png" alt="A world map"/>-->
                        <a href="<?php echo get_permalink(icl_object_id(38, 'page')); ?>/region/americas" title="Futurestep offices in the Americas" class="americas map-area"><span class="hidden">Americas</span></a>
                        <a href="<?php echo get_permalink(icl_object_id(38, 'page')); ?>/region/apac" title="Futurestep offices in Asia Pacific" class="asiapac map-area"><span class="hidden">Asia Pacific</span></a>
                        <a href="<?php echo get_permalink(icl_object_id(38, 'page')); ?>/region/emea" title="Futurestep offices in Europe, Middle East, Africa" class="emea map-area"><span class="hidden" class="emea">EMEA</span></a>
                    </div>
                </div>
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
                            <?php if($term->parent != 0) :?><option value="<?php echo $term->slug; ?>"<?php if($term->name==ICL_LANGUAGE_NAME) echo ' selected="selected"'; ?>><?php echo $term->name; ?></option><?php endif; ?>
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
                            <option value="<?php echo $term->slug; ?>"><?php echo $term->name; ?></option>
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
                            <option value="<?php echo $term->slug; ?>"><?php echo $term->name; ?></option>
            <?php $args = array( 'taxonomy' => 'category-solution', 'hide_empty' => false, 'parent' => $term->term_id );
                $terms2 = get_terms('category-solution', $args);
                $x=0;
                foreach($terms2 as $term2) : ?>
                            <option value="<?php echo $term2->slug; ?>"><?php echo "-".$term2->name; ?></option>
                <?php  endforeach; ?>
            <?php  endforeach; ?>
                        </select>
                    </label>
                      <input type="submit" name="find-expert" value="<?php echo icl_t('Search button', 'Search', 'Search'); ?>" class="button"/>
                </form>
</aside>