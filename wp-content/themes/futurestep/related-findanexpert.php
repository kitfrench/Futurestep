<?php
global $post;
$terms = get_the_terms( $post->ID, 'category-sector' );
$sector_links = array();
if($terms) {
    foreach ( $terms as $term ) {
		  $sector_links[] = $term->slug;
	   }
	  $sector = join( ", ", $sector_links );
	} else {
    $sector='';
  }

$terms = get_the_terms( $post->ID, 'category-solution' );
$services_links = array();
if($terms) {
	 foreach ( $terms as $term ) {
	   	$services_links[] = $term->slug;
	 }
	 $services = join( ", ", $services_links );
	 } else {
      $services='';
    }

$terms = get_the_terms( $post->ID, 'category-region' );
$country_links = array();
  if($terms) {
	 foreach ( $terms as $term ) {
	   	$country_links[] = $term->slug;
	 }
	 $country = join( ", ", $country_links );
	 } else {
      $country='';
    }
?>
<div class="summary">
    <h1><?php echo icl_t('Related', 'Contact your local specialist', 'Contact your local specialist'); ?></h1>

    <form action="<?php echo get_permalink(icl_object_id(303, 'page')); ?>" method="post">
                    <label>
                        <span><?php echo icl_t('Job Form', 'Select a country', 'Select a country'); ?></span>
                        <select name="country">
                            <option value=""><?php echo icl_t('Job Form', 'Select a country', 'Select a country'); ?></option>
          <?php 
            $args = array( 'taxonomy' => 'category-region' );
            $terms = get_terms('category-region', $args);
            foreach($terms as $term) : ?>
                            <?php if($term->parent != 0) :?><option value="<?php echo $term->slug; ?>"><?php echo $term->name; ?></option><?php endif; ?>
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
</div>