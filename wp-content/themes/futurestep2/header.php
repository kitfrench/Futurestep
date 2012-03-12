<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <title><?php bloginfo('name'); ?> <?php wp_title(":",true); ?></title>
  <?php get_template_part("futurestep","scripts");?>
  <?php wp_head(); ?>
</head>
<body>
    <div class="content">
    <?php get_template_part("futurestep","navigation");?>
