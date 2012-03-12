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
                        <li><a href="<?php echo get_permalink(506); ?>"><?php echo get_the_title(506); ?></a></li>
                        <?php query_posts("post_type=page&post_parent=506"); 
                        while ( have_posts() ) : the_post(); ?>
                        <li<?php if($post->post_name=='leaders') echo ' class="selected" '; ?>><a title="<?php the_title(); ?>" href="<?php the_permalink(); ?>"><?php echo the_title(); ?></a></li>
                        <?php endwhile;
                        // Reset Query
                        wp_reset_query(); ?>
                    </ul>
                </nav>
                        <?php else : ?>
                <nav class="index experts-index">
                    <ul>
                        <li><a href="/contact-us">Global Offices</a></li>
                        <li class="selected last-item"><a href="/find-an-expert">Find an
                            Expert</a>
                        </li>
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
                            <h2>Location</h2>

                            <div id="map-container-expert">

                            </div>
                        </section>
<?php endif; ?>
                    </div>
                </section>
                <aside class="features">
                    <?php if(is_null($_GET['l'])) : ?>
                    <aside class="summary">
                        <h1>Contact your local specialist</h1>

                        <form action="/find-an-expert" method="post">
                <label>
                    <span>Select a Country</span>
                <select name="country">
                    <option value="">Select a Country</option>
          <?php 
            $args = array( 'taxonomy' => 'category-region' );
            $terms = get_terms('category-region', $args);
            $x=0;
            foreach($terms as $term) : ?>
                    <?php if($term->parent != 0) :?><option <?php if(strripos( $country , $term->name) && $x==0) {$x=1; echo 'selected="selected" ';} ?>value="<?php echo $term->name; ?>"><?php echo $term->name; ?></option><?php endif; ?>
          <?php  endforeach; ?>
                </select>
                </label>
                <label>
                    <span>Select a Sector</span>
                <select name="sector">
                    <option value="">Select a Sector</option>
          <?php 
            $args = array( 'taxonomy' => 'category-sector' );
            $terms = get_terms('category-sector', $args);
            $x=0;
            foreach($terms as $term) : ?>
                    <option <?php if(!is_null(strripos( $sector , $term->name)) && $x==0) {$x=1; echo 'selected="selected" ';} ?>value="<?php echo $term->name; ?>"><?php echo $term->name; ?></option>
          <?php  endforeach; ?>
                </select>
                </label>
                <label>
                    <span>Select a Solution</span>
                <select name="solution">
                    <option value="">Select a Service</option>
          <?php 
            $args = array( 'taxonomy' => 'category-solution' );
            $terms = get_terms('category-solution', $args);
            $x=0;
            foreach($terms as $term) : ?>
                    <?php if($term->parent != 0) :?><option <?php if($_POST['solution']==$term->name) echo 'selected="selected" '; ?>value="<?php echo $term->name; ?>"><?php echo "-".$term->name; ?></option><?php else : ?>
                    <option <?php if(!is_null(strripos($services, $term->name)) && $x==0) {$x=1; echo 'selected="selected" ';} ?>value="<?php echo $term->name; ?>"><?php echo $term->name; ?></option><?php endif; ?>
          <?php  endforeach; ?>
                </select>
                </label>
                <input type="submit" name="find-expert" value="Search" class="button"/>
                   </form>
                    </aside>
          <?php else : ?>          
                    <aside class="summary">
                    <h1>Regional leadership</h1>

                    <form action="/about-us/leaders" method="post">
                <label>
                    <span>Select a Country</span>
                <select name="country">
                    <option value="">Select a Country</option>
          <?php 
            $args = array( 'taxonomy' => 'category-region' );
            $terms = get_terms('category-region', $args);
            foreach($terms as $term) :  ?>
                    <?php if($term->parent!=0) : ?><option <?php if($_POST['country']==$term->name) echo 'selected="selected" '; ?>value="<?php echo $term->name; ?>"><?php echo $term->name; ?></option><?php endif; ?>
          <?php  endforeach; ?>
                </select>
                </label>
                <input type="submit" name="find-expert" value="Search" class="button"/>
                   </form>
                </aside>
          <?php endif; ?> 
                </aside>
            </section>

        </section>
    </div>
<?php get_footer(); ?>
