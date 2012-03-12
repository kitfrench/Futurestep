<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <title>Futurestep : Services - RPO</title>
  <?php include("includes/default-libs.php"); ?>
    <?php
        $utils=new Utils();
        $utils->setCurrentSection("services");
    ?>
</head>
<body id="homepage">
<div class="content">
<?php include("includes/header.php"); ?>

  <div class="core-content-container bridge-illustration">
    <section class="core-content">
      <aside class="detail">
        <section class="body">
          <h1>Recruitment Process Outsourcing</h1>

          <p>Duis aliquet egestas purus in blandit. Curabitur vulputate, ligula lacinia scelerisque tempor, lacus lacus
            ornare ante, ac egestas est urna sit amet arcu. Class aptent taciti sociosqu ad litora torquent per conubia
            nostra, per inceptos himenaeos. Sed molestie augue sit amet leo consequat posuere. Vestibulum ante ipsum
            primis in faucibus orci luctus et ultrices posuere cubilia Curae; Proin vel ante a orci tempus eleifend ut
            et magna. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus luctus urna sed urna ultricies ac
            tempor dui sagittis. In condimentum facilisis porta. Sed nec diam eu diam mattis viverra. Nulla fringilla,
            orci ac euismod semper, magna diam porttitor mauris, quis sollicitudin sapien justo in libero. Vestibulum
            mollis mauris enim. Morbi euismod magna ac lorem rutrum.</p>

          <p>Morbi malesuada nulla nec purus convallis consequat. Vivamus id mollis quam. Morbi ac commodo nulla. In
            condimentum orci id nisl volutpat bibendum. Quisque commodo hendrerit lorem quis egestas. Maecenas quis
            tortor arcu.</p>

          <ul>
            <li>Aenean facilisis nulla vitae urna tincidunt congue sed ut dui</li>
            <li>Vivamus luctus urna sed urna ultricies</li>
            <li>Etiam scelerisque, nunc ac egestas</li>
            <li>Mauris iaculis porttitor posuere. Praesent id</li>
          </ul>

          <h2>Nulla At Nulla</h2>

          <p>Praesent eget neque eu eros interdum malesuada non vel leo. Sed fringilla porta ligula egestas tincidunt.
            Nullam risus magna, ornare vitae varius eget, scelerisque a.</p>

        </section>
        <aside class="features">
          <article class="summary">
            <h1>Expertise</h1>
            <blockquote>Futurestep Strategic RPO Delivers Long Term Value for the World's Largest Nuclear Facility</blockquote>
            <a href="./insights-article.php">Journal of Corporate Recruiting Leadership</a>
            <time datetime="2011-07-26T12:00:00">26 July 2011 12:00</time>
          </article>
          <article class="summary">
            <h1>Experience</h1>
            <img src="img/logos/bruce-power-logo.jpg" alt="A logo for Bruce Power"/>
            <a href="./insights-article.php">Futurestep Strategic RPO Delivers Long Term Value for the World's Largest Nuclear Facility</a>
          </article>
        </aside>
      </aside>
      <aside class="summaries related-content">
        <div class="summary-group">
          <h1>Latest News and Events</h1>
          <article class="summary">
            <a class="title" href="#!/somelink/somewhere">Maximise Your Investment: Implement RPO to Align with Business Goals</a>
            <time datetime="2011-07-26">26 July 2011 12:00</time>
            <a href="#!/news-and-events/register" title="Register for an event" class="more-detail">Register for
              event</a>
              <a href="./events-calendar.php" class="more-detail">View all events</a>
          </article>
        </div>

        <div class="summary">
          <h1>Contact your local RPO specialist</h1>
        <form>
            <label>
                <span>Select a Country</span>
                <select>
                    <option>Select a country</option>
                    <option>United States</option>
                    <option>United Kingdom</option>
                    <option>France</option>
                    <option>Germany</option>
                </select>
            </label>
            <label>
                <span>Select a Sector</span>
                <select>
                    <option>Select a sector</option>
                    <option>Sector one</option>
                    <option>Sector two</option>
                </select>
            </label>

            <input type="button" value="Search" class="button"/>
        </form>
        </div>
      </aside>
    </section>
  </div>
<?php include("includes/footer.php"); ?>
</div>
</body>
</html>