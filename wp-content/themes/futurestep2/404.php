<?php get_header(); ?>
<?php 
global $post;
setup_postdata($post);
?>
<div class="core-content-container four-o-four">
    <section class="core-content">
      <div class="message">
        <hgroup>
          <h1>Sorry about this...</h1>
          <h2>The page you are looking for could not be found!</h2>

        </hgroup>
        <p>
          Sometimes pages move or go out of date and get deleted. It's possible that the page you are looking for has
          either changed names or is no longer available. Please visit our <a href="./home.html">homepage</a> 
          or <a href="./sitemap.html">site map</a> for the most up-to-date
          list of our pages.
        </p>
      </div>
    </section>

  </div>
<?php get_footer(); ?>