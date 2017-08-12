<?php
include 'pageHeader.php';
htmlStart('Visions for Learning');
?>
<!--
    <button type="button" class="btnStory">Stories</button>
    <button type="button" class="btnLesson">Lessons</button>
    <button type="button" class="btnBlog">Blog</button>
    <button type="button" class="btnDiscuss">Discussion</button>
    -->
<div class="btn-group btn-group-justified">
    <a href="index.php" class="btn btn-primary" title="Cohesive units and the stories, lessons, worksheets and quizzes that support them.">Find Content</a>
    <a href="contentEdit.php" class="btn btn-primary" title="Let your knowledge and creativity spread.">Contribute Content</a>
    <a href="index.php" class="btn btn-primary">Articles</a>
    <a href="index.php" class="btn btn-primary">Discussion</a>
  <?php
    if ($_SESSION['isTagEditor'] || $_SESSION['isSuperUser']) {
        echo '<a href="./admin.php" class="btn btn-default">Admin</a>';
    }
  ?>

</div>

<br /><br />
<div class="container">
    <h1 align="center">Visions for Learning Stories and Lessons<br /><br /></h1>
    <div id="homeCarousel" class="carousel slide" data-ride="carousel" style="background-color:lightgray;padding:30px"
        <!-- Indicators -->
        <ol class="carousel-indicators">
            <li data-target="#homeCarousel" data-slide-to="0" class="active"></li>
            <li data-target="#homeCarousel" data-slide-to="1"></li>
            <li data-target="#homeCarousel" data-slide-to="2"></li>
        </ol>

        <!-- Wrapper for slides -->
        <div class="carousel-inner">
            <div class="item active">
                <h3 align="center"><b>Our Mission:</b><br />
                    To create a curriculum design and support community that continuously improves learning experiences for use by teachers, students, curriculum designers and artists.<br /><br /></h3>
            </div>

            <div class="item">
                <h3 align="center"><b>Our Belief:</b><br />
                    Local communities have the sensitivity, intelligence, and talent to create great learning experiences, especially when supported by global resources.<br /><br /></h3>
            </div>

            <div class="item">
                <h3 align="center"><b>On Funding:</b><br />
                    No one will ever have to pay to use these resources. Donations will pay our work forward so everyone can benefit from all of our efforts.<br /><br /></h3>
            </div>
        </div>

        <!-- Left and right controls
        <a class="left carousel-control" href="#homeCarousel" data-slide="prev">
            <span class="glyphicon glyphicon-chevron-left"></span>
            <span class="sr-only">Previous</span>
        </a>
        <a class="right carousel-control" href="#homeCarousel" data-slide="next">
            <span class="glyphicon glyphicon-chevron-right"></span>
            <span class="sr-only">Next</span>
        </a>
        -->
    </div>
</div>

</body>
</html>

