<aside id="global-network" class="summaries">
            <div class="summary">

                <h1>Global Network</h1>
                <a class="title" href="<?php bloginfo('siteurl'); ?>/contact-us">38 offices in 17 countries with 500+ professionals</a>
                <div class="map-container">
                    <div class="map">
                        <!--<img src="<?php bloginfo('template_url'); ?>/img/dummyimg_worldmap.png" alt="A world map"/>-->
                        <a href="#!/region/americas" title="Futurestep offices in the Americas" class="americas map-area"><span class="hidden">Americas</span></a>
                        <a href="#!/region/asia-pacific" title="Futurestep offices in Asia Pacific" class="asiapac map-area"><span class="hidden">Asia Pacific</span></a>
                        <a href="#!/region/emea" title="Futurestep offices in Europe, Middle East, Africa" class="emea map-area"><span class="hidden" class="emea">EMEA</span></a>
                    </div>
                </div>
                <h1>Find an expert</h1> 

                <form action="<?php bloginfo('siteurl'); ?>/find-an-expert" method="post">
                    <label>
                        <span>Select a Country</span>
                        <select name="country">
                            <option value="">Select a country</option>
          <?php 
            $args = array( 'taxonomy' => 'category-region' );
            $terms = get_terms('category-region', $args);
            foreach($terms as $term) : ?>
                            <?php if($term->parent != 0) :?><option value="<?php echo $term->name; ?>"><?php echo $term->name; ?></option><?php endif; ?>
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
                            <option value="<?php echo $term->name; ?>"><?php echo $term->name; ?></option>
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
                            <option value="<?php echo $term->name; ?>"><?php echo $term->name; ?></option><?php endif; ?>
          <?php  endforeach; ?>
                        </select>
                    </label>
                      <input type="submit" name="find-expert" value="Search" class="button"/>
                </form>
</aside>