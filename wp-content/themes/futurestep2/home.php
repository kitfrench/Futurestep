<?php /* Template Name: home */ ?>
<?php get_header(); ?>
<?php get_template_part( 'home', 'introduction' );?>
<div class="core-content-container">
    <section class="core-content">
    <?php  get_template_part( 'home', 'latestinsights' ); ?>
    <?php get_template_part( 'home', 'latestnews' ); ?>
    <?php get_template_part( 'home', 'globalnetwork' ); ?>
    </section>
</div>

<?php get_footer(); ?>
