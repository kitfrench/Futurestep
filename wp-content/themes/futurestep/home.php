<?php /* Template Name: home */ ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="description"
          content="Discover the best talent with Futurestep's recruitment process outsourcing (RPO) solutions.  Global recruitment services with local sensitivity.  Contact us today."/>
    <meta name="keywords"
          content="recruitment services, rpo, recruitment process outsourcing, global recruitment, recruitment technology, futurestep, korn ferry, korn ferry international"/>
    <title>Global recruitment services, recruitment process outsourcing, RPO, recruitment consulting -
        Futurestep</title>
    <?php get_template_part("futurestep","scripts");?>
    <?php wp_head(); ?>
</head>
<body>
<div class="content">
    <?php get_template_part("futurestep","navigation");?>
    <?php get_template_part( 'home', 'introduction' );?>
    <div class="core-content-container">
        <section class="core-content">
            <?php  get_template_part( 'home', 'latestinsights' ); ?>
            <?php get_template_part( 'home', 'latestnews' ); ?>
            <?php get_template_part( 'home', 'globalnetwork' ); ?>
        </section>
    </div>

    <?php get_footer(); ?>
