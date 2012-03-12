<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <title>Futurestep : News and Events</title>
    <?php include("includes/default-libs.php"); ?>
    <?php
        $utils=new Utils();
        $utils->setCurrentSection("news");
    ?>
</head>
<body>
<div class="content">
  <?php include("includes/header.php"); ?>

  <section class="introduction">
    <div class="headline">
      <h1><a href="#link-to-article"> Maximising Your Investment:<br/>
        Implement RPO to align with you business</a></h1>

      <p>
        <time datetime="01/01/2011T12:00:00">01/01/2011</time>
        Jeanne McDonald, Paul Flevin</p>

      <a href="./events-calendar.php">View event calendar</a>
    </div>
  </section>
  <div class="core-content-container">
    <section class="core-content">
      <aside class="summaries">
        <div class="summary-group only-child">
          <h1>In the News</h1>
          <article class="summary">
            <a class="title" href="#!/somelink/somewhere">Major New Zealand Dairy Exporter and Futurestep RPO Client
              Fonterra to Speak at HRO Summit APAC</a>

            <p>
              <time datetime="2011-09-02">08/26/2011</time>
            </p>
          </article>

          <a href="#!/insights" class="more-detail">View all news</a>
        </div>
      </aside>
      <aside class="summaries">
        <div class="summary-group only-child">
          <h1>Press Releases</h1>
          <article class="summary">
            <a class="title" href="#!/somelink/somewhere">Futurestep Expands EMEA Leadership operations to Adress Growth
              in Demand</a>

            <p>
              <time datetime="01/01/2011 12:00:00">26 July 2011 12:00</time>
            </p>
          </article>
          <article class="summary">
            <a class="title" href="#!/somelink/somewhere">Futurestep appoints Roberto Spuri as Managing Director, Latin
              America</a>

            <p>
              <time datetime="01/01/2011 12:00:00">28 July 2011 12:00</time>
            </p>
          </article>

          <a href="./press-releases.php" title="View all press releases" class="more-detail">View all press
            releases</a>
        </div>
      </aside>
      <aside class="summaries">
        <div class="summary-group only-child">
          <h1>Sign up to hear more</h1>

          <p>Receive updates on white papers, articles, and other valuable thought leadership resources covering all
            facets of talent acquisition and management.</p>
          <a href="#!/news-and-events/register" title="View all new and events" class="button">Sign up</a>
        </div>

      </aside>
    </section>
  </div>

  <?php include("includes/footer.php"); ?>
    <div class="introduction-illustration-container">
        <div class="introduction-illustration banner-cityscape-illustration"></div>
    </div>
</div>
</body>
</html>