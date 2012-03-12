<div class="summary">
    <h1>Job opportunities with our clients</h1>
    <?php
            $feeds=array( 'Technology' =>'http://careers.futurestep.com/xml/category257381.xml',
            'Industrial' => 'http://careers.futurestep.com/xml/category257484.xml',
            'Government & Legal Industry Careers' => 'http://careers.futurestep.com/xml/category257333.xml',
            'Healthcare & Life Sciences' => 'http://careers.futurestep.com/xml/category257334.xml',
            'Consumer' =>'http://careers.futurestep.com/xml/category257422.xml',
            'Financial' =>'http://careers.futurestep.com/xml/category257330.xml');
            ?>
    <section class="jobs-slider">
    <div class="jobs-slider-container">
        <ul class="jobs">
            <?php foreach($feeds as $feedtype => $feedaddress) :
                    $jobs=(getJobDetails($feedtype, $feedaddress)); ?>
            <li class="job" style="overflow-y:hidden;">
                <span class="sector"><?php echo $feedtype; ?></span>
                <a href="<?php echo $jobs[$feedtype]['guid']; ?>" class="title"><?php echo $jobs[$feedtype]['title']; ?></a>
                <a href="<?php echo $jobs[$feedtype]['guid']; ?>" class="apply">Apply for this job</a>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <div>
        <a href="#!/carousel/item/next" class="sprite nextprev-small prev-small"><span>previous</span></a>
        <ul class="slider-index">
            <?php
                    $x=1;
                    foreach($feeds as $feedtype => $feedaddress) : ?>
            <li <?php if($x==1) echo ' class="selected"'; ?>><a href="#!/carousel/item/<?php echo $x-1; ?>"
                                                            class="decoration sprite"><span><?php echo $x; ?></span></a>    </li>
        <?php $x++; endforeach; ?>
    </ul>
    <a href="#!/carousel/item/next" class="sprite nextprev-small next-small"><span>next</span></a>
</div>
        </section>
<a href="http://careers.futurestep.com" class="continue" target="new">See our most recent roles <span
        class="decoration sprite right-arrow-grey"><span>&gt;</span></span></a>
        </div>
