<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <title>Futurestep : Services - Employer branding</title>
  <?php include("includes/default-libs.php"); ?>
    <?php
        $utils=new Utils();
        $utils->setCurrentSection("services");
    ?>
</head>
<body>
<div class="content">
  <?php include("includes/header.php"); ?>

  <div class="core-content-container bridge-illustration">
    <section class="core-content">
      <aside class="detail">
          <nav class="index">
              <ul>
                  <li class="selected"><a href="#!/services/employer-branding/s-and-a-overview">Sourcing &amp; Attraction Overview</a></li>
                  <li><a href="#!/services/employer-branding/recruitment-communications">Recruitment Communications</a></li>
                  <li class="last-item"><a href="#!/services/employer-branding/sourcing">Sourcing</a></li>
              </ul>
          </nav>
        <section class="body">
          <h1>Sourcing and Attraction Ovierview</h1>

          <p>Duis aliquet egestas purus in blandit. Curabitur vulputate, ligula lacinia scelerisque tempor, lacus lacus
            ornare ante, ac egestas est urna sit amet arcu. Class aptent taciti sociosqu ad litora torquent per conubia
            nostra, per inceptos himenaeos. Sed molestie augue sit amet leo consequat posuere. Vestibulum ante ipsum
            primis in faucibus orci luctus et ultrices posuere cubilia Curae; Proin vel ante a orci tempus eleifend ut
            et magna. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus luctus urna sed urna ultricies ac
            tempor dui sagittis. In condimentum facilisis porta. Sed nec diam eu diam mattis viverra. Nulla fringilla,
            orci ac euismod semper, magna diam porttitor mauris, quis sollicitudin sapien justo in libero. Vestibulum
            mollis mauris enim. Morbi euismod magna ac lorem rutrum.</p>
        </section>
        <section class="features">
          <aside class="summary">
            <h1>More on this Service</h1>

            <dl class="hcard contact">
                <dt>Contact</dt><dd class="fn">Joe Smith</dd>
                <dt>Phone Number</dt><dd class="tel">0207 123 0987</dd>
                <dt>Email</dt><dd><a class="email" href="mailto:my.email@futurestep.com">my.email@futurestep.com</a></dd>
            </dl>
          </aside>
          <aside class="summary">
              <h1>Key Downloads</h1>
              <article>
                  <a href="#!/pdf/some-pdf.pdf">Identifying and collecting EVP Data</a>
                  <p>(256Kb PDF)</p>
              </article>
              <article>
                  <a href="#!/pdf/some-pdf.pdf">Analysis and Benchmake Comparison Strategy</a>
                  <p>(1,128Kb PDF)</p>
              </article>
          </aside>
        </section>
      </aside>
      <aside class="summaries related-content">
        <div class="summary-group">
          <h1>Related Links</h1>
          <article class="summary">
            <a class="title" href="./insights-article.php">Looking Beyond Reactive Fixes: How do you Make Talent Acquisition Truly Better</a>
            <p>Journal of Corporate Recruiting, <time datetime="2011-07-26">26 July 2011 12:00</time> </p>
          </article>
          <article class="summary">
            <a class="title" href="./insights-article.php">Making Talent Acquisition Truly Better</a>
            <p>Georde Hoffmaster, Practive Manager, Futurestep, <time datetime="2011-07-26">26 July 2011 12:00</time> </p>
          </article>
        </div>
      </aside>
    </section>
  </div>

  <?php include("includes/footer.php"); ?>
</div>
</body>
</html>