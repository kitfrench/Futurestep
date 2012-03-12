<?php /* Template Name: Solutions - Bridge */ ?>
<?php get_header();  
global $post;
setup_postdata($post);
$post_id=get_the_ID();

$terms = get_the_terms( $post->ID, 'category-sector' );
	if($terms) {
    foreach ( $terms as $term ) {
		  $sector_links[] = $term->name;
	   }
	  $sector = join( ", ", $sector_links );
	} else {
    $sector='';
  }

$terms = get_the_terms( $post->ID, 'category-solution' );
  if($terms) {
	 foreach ( $terms as $term ) {
	   	$services_links[] = $term->name;
	 }
	 $services = join( ", ", $services_links );
	 } else {
      $services='';
    }

$terms = get_the_terms( $post->ID, 'category-region' );
  if($terms) {
	 foreach ( $terms as $term ) {
	   	$country_links[] = $term->name;
	 }
	 $country = join( ", ", $country_links );
	 } else {
      $country='';
    }
?>
<div class="core-content-container bridge-illustration">
        <section class="core-content about-us-container">
            <section class="detail">
                <?php if(!is_null($_GET['l'])) : ?>
                <nav class="index">
                    <ul>
                        <li><a href="<?php echo get_permalink($post->post_parent); ?>"><?php echo get_the_title(506); ?></a></li>
                        <?php wp_list_pages("title_li=&child_of=506"); ?>
                    </ul>
                </nav>
                        <?php else : ?>
                <nav class="index experts-index">
                    <ul>
                        <li><a href="<?php echo get_permalink(icl_object_id(38, 'page')); ?>"><?php echo icl_t('Contact', 'Global offices', 'Global offices'); ?></a></li>
                        <li class="selected last-item"><a href="<?php echo get_permalink(icl_object_id(303, 'page')); ?>"><?php echo icl_t('Contact', 'Find an Expert', 'Find an Expert'); ?></a></li>
                    </ul>
                </nav>
                        <?php endif; ?>
                <section class="body">
                    <div class="expert-profile">
                        <section class="vcard">
                            <?php the_post_thumbnail( array (109,128), array('class'=> 'photo')); ?>

                            <p>
                                <span class="n"><?php the_title(); ?></span><br/>
                                <span><?php echo get_post_meta(get_the_ID(), 'expert-position', true) ?></span><br/>
                                
                                <?php if(get_post_meta(get_the_ID(), 'expert-position-2', true)!='') :?><span><?php echo get_post_meta(get_the_ID(), 'expert-position-2', true) ?></span><br/><?php endif; ?>
                                <?php if(get_post_meta(get_the_ID(), 'expert-organisation', true)!='') : ?><span class="org"><?php echo get_post_meta(get_the_ID(), 'expert-organisation', true) ?></span><br/><?php endif; ?>
                                <span class="locality"><?php echo get_post_meta(get_the_ID(), 'expert-locality', true) ?></span><br/>
                                <?php if(get_post_meta(get_the_ID(), 'expert-telephone', true)!='') : ?><span class="telephone"><?php echo get_post_meta(get_the_ID(), 'expert-telephone', true) ?></span><br/><?php endif; ?>
                                <a href="mailto:<?php echo get_post_meta(get_the_ID(), 'expert-email', true) ?>" class="email"><?php echo get_post_meta(get_the_ID(), 'expert-email', true) ?></a>
                            </p>
                        </section>
                        <section class="bio">
                            <?php the_content(); ?>
                        </section>
<?php if(get_post_meta(get_the_ID(), 'expert-latitude', true) !='' && get_post_meta(get_the_ID(), 'expert-longitude', true) !='') : ?>
                        <section>
                            <h2><?php echo icl_t('Single Events', 'Location', 'Location'); ?></h2>

                            <div id="map-container-expert">

                            </div>
                        </section>
<?php endif; ?>
                    </div>
                </section>
                <aside class="features">
                    <?php if(is_null($_GET['l'])) : ?>
                    <?php get_template_part('related', 'findanexpert'); ?>
          <?php else : ?>          
                    <aside class="summary">
                    <h1><?php echo icl_t('Leadership', 'Regional Leadership', 'Regional Leadership'); ?></h1>

                    <form action="<?php echo get_permalink(icl_object_id(516, 'page')); ?>" method="post">
                <label>
                    <span><?php echo icl_t('Job Form', 'Select a country', 'Select a country'); ?></span>
                <select name="country">
                    <option value=""><?php echo icl_t('Leadership', 'Global', 'Global'); ?></option>
          <?php 
            $args = array( 'taxonomy' => 'category-region' );
            $terms = get_terms('category-region', $args);
            foreach($terms as $term) :  ?>
                    <?php if($term->parent==0 && $term->name!='Global') : ?><option <?php if($_POST['country']==$term->name) echo 'selected="selected" '; ?>value="<?php echo $term->name; ?>"><?php echo $term->name; ?></option><?php endif; ?>
          <?php  endforeach; ?>
                </select>
                </label>
                <input type="submit" name="find-expert" value="<?php echo icl_t('Search button', 'Search', 'Search'); ?>" class="button"/>
                   </form>
                </aside>
          <?php endif; ?> 
                </aside>
            </section>

        </section>
    </div>
<?php get_footer(); ?>
