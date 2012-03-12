<footer>
    <nav>
        <div class="company-logos">
            <a href="<?php echo get_bloginfo('siteurl'); ?>" class="futurestep-logo-grey"><span class="hidden">Futurestep - a Korn Ferry Company</span></a>
            <a href="http://www.kornferry.com/" target="_blank" class="korn-ferry-logo-grey"><span class="hidden">Korn Ferry International</span></a>
        </div>

        <ul>
            <?php wp_list_pages("title_li=&child_of=38"); ?>
        </ul>

        <ul>
            <?php wp_list_pages("include=38&title_li="); ?>
        </ul>

        <ul class="social-media">
            <li>
                <a href="http://www.twitter.com/futurestep" target="new" title="Follow Futurestep on Twitter" class="twitter"><span>Twitter</span></a>

            </li>
            <li>
                <a href="http://www.linkedin.com/company/3740?goback=%2Efcs_GLHD_futurestep_false_*2_*2_*2_*2_*2_*2_*2_*2_*2_*2_*2_*2&trk=ncsrch_hits" target="new" title="Connect with Futurestep on LinkedIn"
                   class="linkedin"><span>LinkedIn</span></a>
            </li>
        </ul>
    </nav>
</footer>
<?php
wp_reset_query();
if(is_page(array('insights', 'news-and-events' , 'working-with-us'))) : ?>
<?php $banner = (get_post_meta($post->ID, 'bannerimage', true)=='') ? 'butterfly' : get_post_meta($post->ID, 'bannerimage', true);?>
    <div class="introduction-illustration-container">
        <div class="introduction-illustration banner-<?php echo $banner; ?>-illustration"></div>
    </div>
<?php endif; ?>
</div>
<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-4103674-1']);
  _gaq.push(['_setDomainName', '.futurestep.com']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>
</body>
</html>