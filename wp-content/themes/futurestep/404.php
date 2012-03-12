<?php get_header(); ?>
<?php 
global $post;
setup_postdata($post);
?>
<div class="core-content-container four-o-four">
    <section class="core-content">
      <div class="message">
        <hgroup>
          <h1><?php echo icl_t('404', 'Sorry about this...', 'Sorry about this...'); ?></h1>
          <h2><?php echo icl_t('404', 'The page you are looking for could not be found!', 'The page you are looking for could not be found!'); ?></h2>

        </hgroup>
        <p>
          <?php echo icl_t('404', '404 - Prose', "Sometimes pages move or go out of date and get deleted. It's possible that the page you are looking for has either changed names or is no longer available. Please visit our <b>homepage</b> or <b>site map</b> for the most up-to-date list of our pages."); ?>
        </p>
        <p><a href="/"><?php echo icl_t('404', 'Homepage', 'Homepage'); ?></a> | <a href="/contact-us/sitemap"><?php echo icl_t('404', 'Site map', 'Site map'); ?></a>
      </div>
    </section>

  </div>
<?php get_footer(); ?>