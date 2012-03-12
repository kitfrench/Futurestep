<section id="carousel">
    <ul class="headlines">
    <?php 
  $carousels = $wpdb->get_results("SELECT * FROM `carousel` ORDER BY `slot`");
  foreach( $carousels as $carousel ) : ?>
    <li class="<?php echo $carousel->image; ?> hero">
            <div class="message">
                <hgroup>
                    <h1><?php echo $carousel->line1; ?></h1>

                    <h1><?php echo $carousel->line2; ?></h1>

                    <h2><?php echo $carousel->line3; ?></h2>
                </hgroup>
                <a href="<?php echo $carousel->link; ?>">Find out more</a>
            </div>
        </li>
  <?php endforeach; ?>
    </ul>
    <ul class="index">
        <li class="selected"><a href="#!/carousel/item/0" class="decoration sprite"><span>1</span></a></li>
        <li><a href="#!/carousel/item/1" class="decoration sprite"><span>2</span></a></li>
        <li><a href="#!/carousel/item/2" class="decoration sprite"><span>3</span></a></li>
        <li><a href="#!/carousel/item/3" class="decoration sprite"><span>4</span></a></li>
    </ul>
</section>