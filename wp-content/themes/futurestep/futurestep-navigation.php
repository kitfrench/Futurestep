<header class="global">
    <nav class="supplementary">
        <ul>
            <li><a href="<?php bloginfo('siteurl'); ?>" title="Find out more about Futurestep"><?php echo icl_t('Header', 'Futurestep home', 'Futurestep home'); ?></a><span class="divider">|</span></li><li>
                <a href="http://candidates.futurestep.com" title="My Futurestep"><?php echo icl_t('Header', 'My futurestep', 'My futurestep'); ?></a></li>
        </ul>
        <a href="<?php bloginfo('siteurl'); ?>/feed" class="rss-link sprite"><?php echo icl_t('Header', 'Subscribe', 'Subscribe'); ?></a>
        <?php #do_action('icl_language_selector'); ?>
    </nav>

    <div class="identity">
        <hgroup>
            <h1 class="logo"><a href="<?php bloginfo('siteurl'); ?>"><span><?php echo icl_t('Header', 'Futurestep home', 'Futurestep home'); ?></span></a></h1>

            <h2><span><?php echo get_bloginfo('desciption'); ?></span></h2>

            <h3 class="strap-line"><span><?php echo icl_t('Header', 'Strapline', 'Talent with impact'); ?></span></h3>
        </hgroup>
    </div>
  <nav class="primary">
    <ul>
      <li><a id="services" href="#!/services" title="List the services offered by Futurestep"><?php echo icl_t('Header', 'Java Drop Down', 'Services'); ?>
                <span class="decoration sprite right-arrow-orange"><span>down</span></span></a></li>

        <?php $pages=get_pages(array( 'parent'=> 0, 'sort_column' => 'menu_order', 'exclude' => array(icl_object_id(5, 'page'),icl_object_id(303, 'page'))));
        foreach ( $pages as $page ) :
        $selected='';
        if(is_page($page->post_name)) $selected=' class="current" ';
        if($page->post_name=='insights' && (is_post_type_archive(array('insight', 'opinion', 'articles')) || is_singular('insight') || is_singular('articles'))) $selected=' class="current" ';
        if($page->post_name=='news-and-events' && (is_post_type_archive(array('news')) || is_singular('news') || is_singular('news-events'))) $selected=' class="current" ';
        if($page->ID== $post->post_parent) $selected=' class="current" ';
        if($page->post_name=='contact-us' && is_page('find-an-expert')) $selected=' class="current" ';
                if($page->post_name=='about-us' && (!is_null($_GET['l']))) $selected=' class="current" ';
        ?>
        <li<?php echo $selected; ?>><a href="<?php echo get_page_link( $page->ID ); ?>"><?php echo $page->post_title; ?></a></li>
        <?php endforeach; ?>

      <li class="candidates">
                <a href="http://careers.futurestep.com" title="Find a job" target="new"><?php echo icl_t('Header', 'Clients\' Jobs', 'Clients\' Jobs'); ?> <span class="decoration sprite right-arrow-grey"><span>&gt;</span></span></a></li>
    </ul>
  </nav>
    <div class="services-navigation-container">
        <nav id="services-navigation">
        <span class="sprite connector-up"></span>

    <?php
      $servicesub= get_pages(array( 'child_of' => icl_object_id(5, 'page'), 'sort_column' => 'menu_order', 'parent' => icl_object_id(5, 'page') ));
      foreach($servicesub as $subpage){
      ?>
            <div class="sector-services">
                <h1><?php echo($subpage->post_title); ?></h1>
                <ul>
                    <?php wp_list_pages('title_li=&depth=1&child_of='.$subpage->ID); ?>
                </ul>
            </div>

      <?php }?>
        </nav>
    </div>
</header>