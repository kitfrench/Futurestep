<?php /* Template Name: default */ ?>
<?php get_header(); ?>
<?php get_template_part( 'home', 'introduction' ); ?>
<section class="core home">
<?php get_template_part( 'home', 'latestinsights' ); ?>
<?php get_template_part( 'home', 'latestnews' ); ?>
<?php get_template_part( 'home', 'globalnetwork' ); ?>
<?php get_template_part( 'home', 'jobswithclients' ); ?>
</section>

<?php get_footer(); ?>
