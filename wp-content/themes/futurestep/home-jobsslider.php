<div class="summary">
    <h1><?php echo icl_t('Home', 'Job Opportunites with our clients', 'Job Opportunites with our clients'); ?></h1>
    <?php 
    global $wpdb;
    
    $querystr="SELECT * FROM feedlocations WHERE `language` = '".ICL_LANGUAGE_CODE."'";
        $feedoutput = $wpdb->get_results($querystr, ARRAY_A);
            ?>
    <section class="jobs-slider">
    <div class="jobs-slider-container">
        <ul class="jobs">
            <?php foreach($feedoutput as $feedtype) :
                    $jobs=(getJobDetails($feedtype['category'], $feedtype['locations']));
            ?>
            <li class="job" style="overflow-y:hidden;">
                <span class="sector"><?php echo $feedtype['category']; ?></span>
                <a href="<?php echo $jobs[$feedtype['category']]['guid']; ?>" class="title"><?php echo $jobs[$feedtype['category']]['title']; ?></a>
                <a href="<?php echo $jobs[$feedtype['category']]['guid']; ?>" class="apply"><?php echo icl_t('Home', 'Apply for this job', 'Apply for this job'); ?></a>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <div>
        <a href="#!/carousel/item/next" class="sprite nextprev-small prev-small"><span>previous</span></a>
        <ul class="slider-index">
            <?php
                    $x=1;
                    foreach($feedoutput as $feedtype) : ?>
            <li <?php if($x==1) echo ' class="selected"'; ?>><a href="#!/carousel/item/<?php echo $x-1; ?>"
                                                            class="decoration sprite"><span><?php echo $x; ?></span></a>    </li>
        <?php $x++; endforeach; ?>
    </ul>
    <a href="#!/carousel/item/next" class="sprite nextprev-small next-small"><span>next</span></a>
</div>
        </section>
<a href="http://careers.futurestep.com" class="continue" target="new"><?php echo icl_t('Home', 'See our most recent roles', 'See our most recent roles'); ?><span
        class="decoration sprite right-arrow-grey"><span>&gt;</span></span></a>
</div>
