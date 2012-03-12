    <?php
        //CHANGEABLE SIDEBAR DEPENDENT ON SIDEBAR CHECKBOXES
        $post_id = get_the_ID();

        if(get_post_meta($post_id, 'latest-new-events', true)) {
            get_template_part('related', 'latestnewsevents');
        }

        if(get_post_meta($post_id, 'find-expert', true)) {
            get_template_part('related', 'findanexpert');
        }

        if(get_post_meta($post_id, 'related-links', true)) {
            get_template_part('related', 'relatedlinks');
        }
    ?>