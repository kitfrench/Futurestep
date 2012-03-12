<section id="carousel">
    <ul class="headlines">
    <?php 
  $carousels = $wpdb->get_results("SELECT * FROM `carousel` WHERE `language` = '".ICL_LANGUAGE_CODE."' ORDER BY `slot`");
  foreach( $carousels as $carousel ) : ?><li class="<?php echo $carousel->image; ?> hero">
            <div class="message">
                <hgroup>
                    <h1><?php echo $carousel->line1; ?></h1>
                    <h1><?php echo $carousel->line2; ?></h1>
                    <h2><?php echo $carousel->line3; ?></h2>
                </hgroup>
                <a href="<?php echo $carousel->link; ?>"><?php echo icl_t('Carousel', 'Find out more', 'Find out more'); ?></a>
            </div>
        </li><?php endforeach; ?>
    </ul>
    <div class="index-container">
      <a href="#!/carousel/item/next" class="sprite nextprev prev"><span>previous</span></a>
      <ul class="index">
          <li class="selected"><a href="#!/carousel/item/0" class="decoration sprite"><span>1</span></a></li>
          <li><a href="#!/carousel/item/1" class="decoration sprite"><span>2</span></a></li>
          <li><a href="#!/carousel/item/2" class="decoration sprite"><span>3</span></a></li>
          <li><a href="#!/carousel/item/3" class="decoration sprite"><span>4</span></a></li>
      </ul>
      <a href="#!/carousel/item/previous" class="sprite nextprev next"><span>next</span></a>
    </div>
</section>
