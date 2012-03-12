<?php
/**
 * The main template file.
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package WordPress
 * @subpackage Twenty_Eleven
 */

get_header(); ?>
<?php get_template_part( 'home', 'introduction' );?>
<div class="core-content-container">
    <section class="core-content">
    <?php  get_template_part( 'home', 'latestinsights' ); ?>
    <?php get_template_part( 'home', 'latestnews' ); ?>
    <?php get_template_part( 'home', 'globalnetwork' ); ?>
    </section>
</div>

<?php get_footer(); ?>