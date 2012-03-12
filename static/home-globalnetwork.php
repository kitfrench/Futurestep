<aside id="global-network" class="summaries">
            <div class="summary">

                <h1>Global Network</h1>

                <div class="world-map">
                    <a class="title" href="<?php bloginfo('siteurl'); ?>/contact-us">38 offices in 17 countries with 500+ professionals</a>
                    <img src="<?php bloginfo('template_url'); ?>/img/dummyimg_worldmap.png" alt="A world map"/>
                </div>
                <h1>Find an expert</h1>

                <form action="<?php bloginfo('siteurl'); ?>/find-an-expert" method="post">
                    <label>
                        <span>Select a Country</span>
                        <select name="country">
                            <option value="">Select a country</option>
          <?php 
            $args = array( 'taxonomy' => 'country' );
            $terms = get_terms('country', $args);
            foreach($terms as $term) :  ?>
                            <option value="<?php echo $term->name; ?>"><?php echo $term->name; ?></option>
          <?php  endforeach; ?>
                        </select>
                    </label>
                    <label>
                        <span>Select a Sector</span>
                        <select name="sector">
                            <option value="">Select a sector</option>
          <?php 
            $args = array( 'taxonomy' => 'sector' );
            $terms = get_terms('sector', $args);
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
            $args = array( 'taxonomy' => 'solution' );
            $terms = get_terms('solution', $args);
            foreach($terms as $term) :  ?>
                            <option value="<?php echo $term->name; ?>"><?php echo $term->name; ?></option>
          <?php  endforeach; ?>
                        </select>
                    </label>
                      <input type="submit" name="find-expert" value="Search" class="button"/>
                </form>
            </div>
</aside>