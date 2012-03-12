<div class="summary">
    <h1><?php echo icl_t('Asides', 'Related Links', 'Related Links'); ?></h1>

    <?php
            $post_id = get_the_ID();

            for ($i = 1; $i <= 3; $i++){

            //    if(get_post_meta($post_id, 'link-'.$i.'-title', true)!=''){ ?>
    <p<?php if($i>1) echo ' class="not-first" '; ?>>
        <a class="title"
           href="<?php echo get_post_meta($post_id, 'link-'.$i.'-address', true) ?>"><?php echo get_post_meta($post_id, 'link-'.$i.'-title', true) ?></a>
        <span><?php echo get_post_meta($post_id, 'link-'.$i.'-source', true) ?></span>
    </p>
        <?php //} ?>
        <?php } ?>
        </div>