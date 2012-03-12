<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <title>Futurestep : Homepage</title>
    <?php include("includes/default-libs.php"); ?>
    <?php
            $utils=new Utils();
            $utils->setCurrentSection("home");
            ?>
    <script src="./js/futurestep-home.js"></script>
</head>
<body>
<div class="content">

    <?php include("includes/header.php"); ?>
    <section id="carousel">
        <ul class="headlines">
            <li class="tree hero">
                <div class="message">
                    <hgroup>
                        <h1>Successful businesses want and need to attract talented people.</h1>

                        <h1>That's where we come in.</h1>

                        <h2>Futurestep. Talent with impact.</h2>
                    </hgroup>
                    <a href="#!/about-us">Find out more</a>
                </div>
            </li>
            <li class="chess hero">
                <div class="message">
                    <hgroup>
                        <h1>Successful businesses want and need to attract talented people.</h1>

                        <h1>That's where we come in.</h1>

                        <h2>Futurestep. Talent with impact.</h2>
                    </hgroup>
                    <a href="#!/about-us">Find out more</a>
                </div>
            </li>
            <li class="butterfly hero">
                <div class="message">
                    <hgroup>
                        <h1>Successful businesses want and need to attract talented people.</h1>

                        <h1>That's where we come in.</h1>

                        <h2>Futurestep. Talent with impact.</h2>
                    </hgroup>
                    <a href="#!/about-us">Find out more</a>
                </div>
            </li>
            <li class="hangglider hero">
                <div class="message">
                    <hgroup>
                        <h1>Successful businesses want and need to attract talented people.</h1>

                        <h1>That's where we come in.</h1>

                        <h2>Futurestep. Talent with impact.</h2>
                    </hgroup>
                    <a href="#!/about-us">Find out more</a>
                </div>
            </li>
        </ul>
        <ul class="index">
            <li class="selected"><a href="#!/carousel/item/0" class="decoration sprite"><span>1</span></a></li>
            <li><a href="#!/carousel/item/1" class="decoration sprite"><span>2</span></a></li>
            <li><a href="#!/carousel/item/2" class="decoration sprite"><span>3</span></a></li>
            <li><a href="#!/carousel/item/3" class="decoration sprite"><span>4</span></a></li>
        </ul>
    </section>

    <div class="core-content-container">
        <section class="core-content">
            <aside id="latest-insights" class="summaries">
                <div class="summary-group">
                    <h1>Latest Insights</h1>
                    <article class="summary">
                        <a class="title" href="#!/somelink/somewhere">The Growing HR Influence in the boardroom</a>

                        <p>Lynne Nixon, Anna Penfold, Ellie Filler, Hal Johnson
                            <time datetime="2011-09-02">08/26/2011</time>
                        </p>
                    </article>
                    <article class="summary">
                        <a class="title" href="#!/somelink/somewhere">Human Capital and Value Creation: Theater or
                            Reality</a>

                        <p>Yannick Binvel, Didier Vichot, Dominique Finelli
                            <time datetime="2011-09-02">02/09/2011</time>
                        </p>
                    </article>
                    <a href="./insights.php" class="more-detail">View all insights</a>
                </div>
                <div class="summary">
                    <h1>Job oppurtunities with our clients</h1>

                    <section class="jobs-slider">
                        <div class="jobs-slider-container">
                        <ul class="jobs">
                            <li class="job">
                                <span class="sector">Technology</span>
                                <a class="title">Mechanical Design Engineer (Albany, NY, USA)</a>

                                <p class="description">Our client is part of a large, innovative and diversified global organisation. It's
                                    Healthcare Sector ranks among the top medical device companies in the world...</p>
                                <a href="#!/apply" class="apply">Apply for this job</a>
                            </li><li class="job">
                                <span class="sector">Another</span>
                                <a class="title">Another Job Detail (London, England, UK)</a>

                                <p class="description">Mauris eu nunc sit amet metus ullamcorper elementum. Vivamus id
                                    rhoncus leo. Nunc iaculis nulla mauris. Pellentesque nec mi diam, et convallis eros.
                                    Curabitur non arcu sapien. Duis malesuada interdum sapien non sagittis</p>
                                <a href="#!/apply" class="apply">Apply for this job</a>
                            </li><li class="job">
                                <span class="sector">A Sector</span>
                                <a class="title">Another Job Detail (Brighton, England, UK)</a>

                                <p class="description">Mauris eu nunc sit amet metus ullamcorper elementum. Vivamus id
                                    rhoncus leo.
                                    Curabitur non arcu sapien. Duis malesuada interdum sapien non sagittis</p>
                                <a href="#!/apply" class="apply">Apply for this job</a>
                            </li><li class="job">
                                <span class="sector">Somethign else</span>
                                <a class="title">Another Job Detail (Brighton, England, UK)</a>

                                <p class="description">Mauris eu nunc sit amet metus ullamcorper elementum. Vivamus id
                                    rhoncus leo.
                                    Curabitur non arcu sapien. Duis malesuada interdum sapien non sagittis</p>
                                <a href="#!/apply" class="apply">Apply for this job</a>
                            </li><li class="job">
                                <span class="sector">Sector 10</span>
                                <a class="title">Another Job Detail (Brighton, England, UK)</a>

                                <p class="description">Mauris eu nunc sit amet metus ullamcorper elementum. Vivamus id
                                    rhoncus leo.
                                    Curabitur non arcu sapien. Duis malesuada interdum sapien non sagittis</p>
                                <a href="#!/apply" class="apply">Apply for this job</a>
                            </li><li class="job">
                                <span class="sector">Some other thing</span>
                                <a class="title">Another Job Detail (Brighton, England, UK)</a>

                                <p class="description">Mauris eu nunc sit amet metus ullamcorper elementum. Vivamus id
                                    rhoncus leo.
                                    Curabitur non arcu sapien. Duis malesuada interdum sapien non sagittis</p>
                                <a href="#!/apply" class="apply">Apply for this job</a>
                            </li>
                        </ul>
                        </div>
                        <ul class="index">
                            <li class="selected"><a href="#!/carousel/item/0" class="decoration sprite"><span>1</span></a></li><li>
                                <a href="#!/carousel/item/1" class="decoration sprite"><span>2</span></a></li><li>
                                <a href="#!/carousel/item/2" class="decoration sprite"><span>3</span></a></li><li>
                                <a href="#!/carousel/item/3" class="decoration sprite"><span>4</span></a></li><li>
                                <a href="#!/carousel/item/4" class="decoration sprite"><span>5</span></a></li><li>
                                <a href="#!/carousel/item/5" class="decoration sprite"><span>6</span></a></li>
                        </ul>
                        <a href="#!/recent-roles/" class="continue">See our most recent roles <span
                                                    class="decoration sprite right-arrow-grey"><span>&gt;</span></span></a>
                    </section>

                </div>
            </aside>
            <aside id="latest-news" class="summaries">
                <div class="summary-group">
                    <h1>Latest News and Events</h1>
                    <article class="summary">
                        <a class="title" href="#!/somelink/somewhere">HCI Webinar: Five Questions for Driving Talent
                            Acquisition
                            Improvement</a>

                        <p>
                            <time datetime="2011-07-26">26 July 2011 12:00</time>
                            , Hilton, San Francisco
                        </p>
                        <a href="#!/news-and-events/register" title="Register for an event" class="more-detail">Register
                            for
                            event</a>
                    </article>

                    <article class="summary">
                        <a class="title" href="#!/somelink/somewhere">TaleoWorld</a>

                        <p>
                            <time datetime="2011-07-26">26 July 2011 12:00</time>
                            , Webinar
                        </p>
                        <a href="./events-calendar.php#!/a-permalink-event-id-to-load" title="Register for an event"
                           class="more-detail">Find out more
                            about the event</a>
                    </article>

                    <a href="./events-calendar.php" title="View all news and events" class="more-detail">View all news
                        and
                        events</a>
                </div>

                <div class="summary">
                    <h1>Working for us</h1>

                    <p>
                        Futurestep has a global presence, with offices across four continents. No matter which one of
                        them
                        interests you, we encourage you to register and start the application process immediately.
                    </p>
                    <a href="#!/find-out/" class="continue">Find out more<span
                            class="decoration sprite right-arrow-grey"><span>&gt;</span></span></a>
                </div>
            </aside>
            <aside id="global-network" class="summaries">
                <div class="summary">
                    <h1>Global Network</h1>

                    <div class="world-map">
                        <a class="title" href="#/contact">38 offices in 17 countries with 500+ professionals</a>
                        <img src="img/dummyimg_worldmap.png" alt="A world map"/>
                    </div>
                    <h1>Find an expert</h1>

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
                        <label>
                            <span>Select a Solution</span>
                            <select>
                                <option>Select a solution</option>
                                <option>Solution one</option>
                                <option>Solution two</option>
                                <option>Solution three</option>
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