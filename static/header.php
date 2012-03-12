<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8"/>
  <title><?php bloginfo('name'); ?> <?php wp_title(":",true); ?></title>
  <link rel="shortcut icon" href="/favicon.ico" />
    <link href="<?php bloginfo('template_url'); ?>/css/html5reset-1.6.1.css" rel="stylesheet"/>
    <link href="<?php bloginfo('template_url'); ?>/css/fonts/webfontkit/stylesheet.css" rel="stylesheet"/>
    <link href="<?php bloginfo('template_url'); ?>/css/styleguide.css" rel="stylesheet"/>
    <script src="<?php bloginfo('template_url'); ?>/js/libs/jquery.1.6.2.js"></script>

    <!--[if lt IE 10]>
    <link href="<?php bloginfo('template_url'); ?>/css/ie-styles.css" rel="stylesheet"/>
    <script src="//html5shim.googlecode.com/svn/trunk/html5.js" type="text/javascript"></script>
    <![endif]-->
    <script src="<?php bloginfo('template_url'); ?>/js/futurestep-main.js"></script>
    <script src="<?php bloginfo('template_url'); ?>/js/futurestep-home.js"></script>
    <script src="<?php bloginfo('template_url'); ?>/js/futurestep-events.js"></script>
    
    <script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?sensor=true"></script>
    <script src="<?php bloginfo('template_url'); ?>js/office-finder.js"></script>

  <!-- php wp_head(); -->
</head>

<body <?php body_class('class-name'); ?>>
<div class="content<?php echo " ".$contentclass; ?>">

<header class="global">
    <nav class="supplementary">
        <ul>
            <li><a href="<?php bloginfo('siteurl'); ?>" title="Find out more about Futurestep">Futurestep home</a></li>

            <!--<li>-->
                <!--<a href="http://candidates.futurestep.com" title="Find out more about Futurestep">Search for a job</a></li>-->
            <li class="last-item">
                <a href="http://careers.futurestep.com" title="career opportunities at Futurestep">Careers at Futurestep</a></li>
        </ul>
        <!--<div class="locale">-->
        <!--<label title="Select your country / Language">-->
        <!--<span>Country / Language</span>-->

        <!--<select>-->
        <!--<option>Global</option>-->
        <!--<option>France</option>-->
        <!--<option>Germany</option>-->
        <!--</select>-->
        <!--</label>-->
        <!--</div>-->
    </nav>

    <div class="identity">
        <hgroup>
            <h1 class="logo"><a href="<?php bloginfo('siteurl'); ?>"><span>Futurestep home</span></a></h1>

            <h2><span>A Korn/Ferry Company</span></h2>

            <h3 class="strap-line"><span>Talent with Impact</span></h3>
        </hgroup>
    </div>

  <nav class="primary">
    <ul>
      <li><a id="services" href="#!/services" title="List the services offfered by Futurestep">Services
                <span class="decoration sprite right-arrow-orange"><span>down</span></span></a></li>
        <?php $pages=get_pages('title_li=&depth=1&exclude=5,303');
        foreach ( $pages as $pagg ) { ?>
      <li class="candidates"><a href="http://candidates.futurestep.com" title="Find out about the different ways to contact Futurestep">Find a job
              <span class="decoration sprite right-arrow-grey"><span>&gt;</span></span></a></li>
    </ul>
  </nav>
    
  
    <div class="services-navigation-container">
        <nav id="services-navigation">
        <span class="sprite connector-up"></span>
 
    <?php
      $servicesub= get_pages(array( 'child_of' => 5, 'sort_column' => 'menu_order', 'parent' => 5 ));
      
      foreach($servicesub as $subpage){
      ?>
            <div class="sector-services">
                <h1><?php echo($subpage->post_title); ?></h1>
                <ul>
                    <?php wp_list_pages('title_li=&depth=1&child_of='.$subpage->ID); ?>
                </ul>
            </div>
        
      <?php }
      
    ?>
<!--    
    <div class="sector-services">
      <h1>By Sector</h1>
      <ul>
        <li><a href="#!/sector/consumer">Consumer</a></li>
        <li><a href="#!/sector/technology">Technology</a></li>
        <li><a href="#!/sector/financial-services">Financial Services</a></li>
        <li><a href="#!/sector/life-sciences">Life Sciences</a></li>
        <li><a href="#!/sector/industrial">Industrial</a></li>
      </ul>
    </div>
-->    
        </nav>
    </div>
    
    <script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?sensor=true"></script>
    <script src="<?php bloginfo('template_url'); ?>/js/office-finder.js"></script>
</header>