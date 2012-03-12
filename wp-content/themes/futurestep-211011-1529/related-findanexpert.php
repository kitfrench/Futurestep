<div class="summary">
    <h1>Contact your local specialist</h1>

    <form action="<?php bloginfo('siteurl'); ?>/find-an-expert" method="post">
        <label>
            <span>Select a Country</span>
            <select name="country">
                <option value="">Select a country</option>
                <?php
                        $args = array( 'taxonomy' => 'category-region' );
                        $terms = get_terms('category-region', $args);
                        foreach($terms as $term) :  var_dump($term); ?>
                <?php if($term->parent != 0) :?>
                <option value="<?php echo $term->name; ?>"><?php echo $term->name; ?></option>
                <?php endif; ?>
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
                <?php if($term->parent != 0) :?>
                <option value="<?php echo $term->name; ?>"><?php echo "-".$term->name; ?></option>
                <?php else : ?>
                <option value="<?php echo $term->name; ?>"><?php echo $term->name; ?></option>
                <?php endif; ?>
                <?php  endforeach; ?>
            </select>
        </label>
        <input type="submit" name="find-expert" value="Search" class="button"/>
    </form>
</div>