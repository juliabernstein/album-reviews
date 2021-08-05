<?php
$title = "Album Reviews";
include("includes/init.php");

$nav_home_class = "current_page";

// set max file size for file uploads
define("MAX_FILE_SIZE", 1000000);

// feedback message CSS classes
$album_feedback_class = 'hidden';
$artist_feedback_class = 'hidden';
$year_feedback_class = 'hidden';
$name_feedback_class = 'hidden';
$rating_feedback_class = 'hidden';
$review_feedback_class = 'hidden';
$file_feedback_class = 'hidden';
$search_feedback_class = 'hidden';

// additional constraints
$review_inserted = False;
$review_insert_failed = False;
$search_inserted = False;
$search_insert_failed = False;

// form values
$search_terms = NULL;

// sticky values
$sticky_search = '';
$sticky_album = '';
$sticky_artist = '';
$sticky_year = '';
$sticky_name = '';
$sticky_rating = '';
$sticky_review = '';
$sticky_source = '';
$sticky_tag = '';

// upload fields
$upload_album = NULL;
$upload_artist = NULL;
$upload_year = NULL;
$upload_name = NULL;
$upload_rating = NULL;
$upload_review = NULL;
$upload_source = NULL;
$upload_filename = NULL;
$upload_ext = NULL;

// set URL
$url = "/?";

// filter mode initially set to false
$filter_mode = False;

// SQL query returns all current tags
$current_tag_names = exec_sql_query(
  $db,
  "SELECT DISTINCT tag_name, tag_id FROM albums LEFT OUTER JOIN album_tags ON albums.id = album_tags.album_id LEFT OUTER JOIN tags ON tags.id= tag_id;"
)->fetchAll();

// if in editing mode
if (isset($_GET['filter-tag'])) {
  $filter_mode = True;

  // set filter id to edit param value
  $filter_id = (int)trim($_GET['filter-tag']);
}


// Did the user submit the form?
if (isset($_POST["submit"])) {


  // untrusted values from form
  $upload_album = trim($_POST["album"]); //untrusted
  $upload_artist = trim($_POST["artist"]); //untrusted
  $upload_year = trim($_POST["year"]); // untrusted
  $upload_name = trim($_POST["name"]); // untrusted
  $upload_rating = trim($_POST["rating"]); // untrusted
  $upload_review = trim($_POST["review"]); // untrusted
  $upload_source = trim($_POST['source']); // untrusted
  $upload_tag = trim($_POST['tag']); // untrusted
  $upload = $_FILES["album-file"];

  // initially, set form_valid to true
  $form_valid = TRUE;

  // if file upload goes through, set filename and extension
  if ($upload['error'] == UPLOAD_ERR_OK) {

     $upload_filename = basename($upload['name']);
     $upload_ext = strtolower( pathinfo($upload_filename, PATHINFO_EXTENSION) );

  } else {
     $form_valid = False;
     echo("error with file");
  }

  // check if required form fields are empty
  // if empty, set form_valid to false and set feedback class

  if (empty($upload_album)) {
    $form_valid = FALSE;
    $album_feedback_class = '';
  } else {
    $album_feedback_class = 'hidden';
  }

  if (empty($upload_artist)) {
    $form_valid = FALSE;
    $artist_feedback_class = '';
  } else {
    $artist_feedback_class = 'hidden';
  }

  if (empty($upload_year)) {
    $form_valid = FALSE;
    $year_feedback_class = '';
  } else {
    $year_feedback_class = 'hidden';
  }

  if (empty($upload_name)) {
    $form_valid = FALSE;
    $name_feedback_class = '';
  } else {
    $name_feedback_class = 'hidden';
  }

  if (empty($upload_rating)) {
    $form_valid = FALSE;
    $rating_feedback_class = '';
  } else {
    $rating_feedback_class = 'hidden';
  }

  if (empty($upload_review)) {
    $form_valid = FALSE;
    $review_feedback_class = '';
  } else {
    $review_feedback_class = 'hidden';
  }

  //  if form is valid, begin SQL query to insert album into database
  if ($form_valid) {
    $db->beginTransaction();

    $result = exec_sql_query($db, "INSERT INTO albums (user_id, album, artist, year, name, rating, review, filename, file_ext, source) VALUES (:user_id, :album, :artist, :year, :name, :rating, :review, :filename, :file_ext, :source) ", array(
      ':user_id' => $current_user['id'],
      ':album' => $upload_album,
      ':artist' => $upload_artist,
      ':year' => $upload_year,
      ':name' => $upload_name,
      ':rating' => $upload_rating,
      ':review' => $upload_review,
      ':filename' => $upload_filename,
      ':file_ext' => $upload_ext,
      ':source' => $upload_source
    ),
    "INSERT INTO tags (tag_name) VALUES (:tag_name);", array (
      ':tag_name'=> NULL
      )
    );

    $length = exec_sql_query($db, "SELECT id FROM albums ORDER BY id DESC LIMIT 1;", array());


    // check if insert query was successful
    if ($result) {

      $add_mode = True;

      // set record id, filename, and move to correct file
      $record_id = $db->lastInsertId('id');
      $id_filename = 'public/uploads/album-covers/' . $record_id . '.' . $upload_ext;
      move_uploaded_file($upload["tmp_name"], $id_filename);

      $review_inserted = True;

    }
    $db->commit();
  } else { // if insert failed, set sticky values

    $review_insert_failed = True;

    $file_feedback_class = '';

    $sticky_album = $upload_album; // tainted
    $sticky_artist = $upload_artist; // tainted
    $sticky_year = $upload_year; // tainted
    $sticky_name = $upload_name; // tainted
    $sticky_rating = $upload_rating; // tainted
    $sticky_review = $upload_review; // tainted
    $sticky_source = $upload_source; // tainted
    $sticky_tag = $upload_tag; // tainted
  }
}

// general SQL query to retrieve all aspects of existing reviews for catalog view
$sql_select_query = "SELECT album_id, albums.id, album, artist, year, name, rating, review, filename, file_ext, source FROM albums LEFT OUTER JOIN album_tags ON albums.id = album_tags.album_id LEFT OUTER JOIN tags ON tags.id=tag_id GROUP BY albums.id; ";
$sql_select_params = array();

// --- Sort ----

if (isset($_GET['sort'])) {
  $sort = $_GET['sort']; // untrusted
}

// valid sorting types
$sort_css_classes = array(
  'high' => '',
  'low' => '',
  'new' => '',
  'old' => ''
);

// if sorting type is valid, make SQL query
if (in_array($sort, array('high', 'low', 'new', 'old'))) {

  $sql_select_query = "SELECT DISTINCT album_id, albums.id, album, artist, year, name, rating, review, filename, file_ext, source FROM albums LEFT OUTER JOIN album_tags ON albums.id = album_tags.album_id LEFT OUTER JOIN tags ON tags.id= tag_id GROUP BY album_tags.album_id";

  // append correct sorting type to SQL query
  if ($sort == 'high') {
    $sql_select_query = $sql_select_query . ' ORDER BY rating DESC;';
    $sort_css_classes['high'] = 'active';
  } else if ($sort == 'low') {
    $sql_select_query = $sql_select_query . ' ORDER BY rating ASC;';
    $sort_css_classes['low'] = 'active';
  } else if ($sort == 'new') {
    $sql_select_query = $sql_select_query . ' ORDER BY year DESC;';
    $sort_css_classes['new'] = 'active';
  } else if ($sort == 'old') {
    $sql_select_query = $sql_select_query . ' ORDER BY year ASC;';
    $sort_css_classes['old'] = 'active';
  }
} else {
  $sort = NULL;
}

// --- Search ---

if (isset($_GET['q'])) {
  // trim spaces
  $search_terms = trim($_GET['q']); // untrusted

  if (empty($search_terms)) {
    $search_terms = NULL;
    $search_feedback_class = '';
  } else {
    $search_feedback_class = 'hidden';
  }

  $sticky_search = $search_terms; // tainted

  // SQL query
  if ($search_terms){

    // search for artist or album
    $sql_select_query = "SELECT album_id, albums.id, album, artist, year, name, rating, review, filename, file_ext, source FROM albums LEFT OUTER JOIN album_tags ON albums.id = album_tags.album_id LEFT OUTER JOIN tags ON tags.id=tag_id GROUP BY albums.id HAVING (album LIKE '%' || :search || '%') OR (artist LIKE '%' || :search || '%');";

    $sql_select_params = array(':search' => $search_terms);

    $search_inserted = True;
  } else {
  }
}

// FILTER

// SQL query to filter by specific tag
if($filter_mode) {
  $filter_query = "SELECT DISTINCT album_id, albums.id, album, artist, year, name, rating, review, filename, file_ext, source FROM albums LEFT OUTER JOIN album_tags ON albums.id = album_tags.album_id LEFT OUTER JOIN tags ON tags.id= tag_id WHERE tag_id = :tag_id GROUP BY albums.id;";
  $filter_params = array(
    ':tag_id'=> $filter_id
  );
  $filter = exec_sql_query($db, $filter_query, $filter_params);
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Album Reviews</title>
  <link rel="stylesheet" type="text/css" href="/public/styles/site.css"/>
</head>

<?php include("includes/header.php"); ?>

<div class="body-div">
  <!-- Form Section -->
  <div class="side-by-side">
    <section class= "album-form">

      <h2 class="header-text"> Want to leave a review? </h2>

      <?php if (!is_user_logged_in()) {
      ?>
        <p class="log-in"> Log in to leave a review. </p>

        <?php echo_login_form("/", $session_messages);
      }?>

      <?php if (is_user_logged_in()) { ?>

        <form id="activityForm" method="post" action="/" enctype="multipart/form-data" novalidate>

          <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo MAX_FILE_SIZE; ?>" />

          <div id="nameFeedback" class="formFeedback <?php echo $name_feedback_class; ?>">Please provide your full name.</div>
            <div class="form-label">
              <label for="request-name"> Your name: </label>
              <input type="text" name="name" id="request-name" class="input-area" value="<?php echo htmlspecialchars($sticky_name); ?>"/>
            </div>

          <div id="albumFeedback" class="formFeedback <?php echo $album_feedback_class; ?>">Please provide an album name.</div>
            <div class="form-label">
              <label for="request-album"> Album name: </label>
              <input type="text" name="album" id="request-album" class="input-area" value="<?php echo htmlspecialchars($sticky_album); ?>"/>
            </div>

          <div id="artistFeedback" class="formFeedback <?php echo $artist_feedback_class; ?>">Please provide an artist name.</div>
            <div class="form-label">
              <label for="request-artist"> Album artist: </label>
              <input type="text" name="artist" id="request-artist" class="input-area" value="<?php echo htmlspecialchars($sticky_artist); ?>"/>
            </div>

          <div id="yearFeedback" class="formFeedback <?php echo $year_feedback_class; ?>">Please provide the release year.</div>
            <div class="form-label">
              <label for="request-year"> Release year: </label>
              <input type="number" name="year" id="request-year" class="input-area" value="<?php echo htmlspecialchars($sticky_year); ?>"/>
            </div>

          <div id="ratingFeedback" class="formFeedback <?php echo $rating_feedback_class; ?>">Please pick a rating.</div>
            <div class="form-label">
              <label for="request-rating"> Rating (out of 10): </label>
              <input type="number" name="rating" id="request-rating" class="input-area" value="<?php echo htmlspecialchars($sticky_rating); ?>"/>
          </div>

          <div id="reviewFeedback" class="formFeedback <?php echo $review_feedback_class;?>"> Please leave a review.</div>
            <div class="form-label">
              <label for="request-review"> Your review: </label>
              <textarea name="review" class="input-area" id="request-review"><?php echo htmlspecialchars($sticky_review);?></textarea>
          </div>

          <div class="reviewFeedback <?php echo $file_feedback_class; ?>">Please select an album cover image file, or select a smaller image file.</div>
          <div class="form-label">
            <label for="upload-file">Album cover image:</label>
            <input id="upload-file" class="input-area" type="file" name="album-file" required />
          </div>

          <div class="form-label">
            <label for="upload-source" class="optional">Source URL:</label>
            <input id='upload-source' class="input-area" type="url" name="source" placeholder="(optional) Album cover image source" value="<?php echo htmlspecialchars($sticky_source); ?>" />
          </div>


          <div class="submit-button">
            <input id="submit-button" type="submit" value="Submit" name="submit"/>
          </div>

        </form>
      <?php } ?>
    </section>
   </div>

  <!-- Review Section -->
  <div class="side-by-side-middle">

    <!-- Introductory text -->
    <?php if (!is_user_logged_in()) { ?>
      <div class="intro-text">
        <h2 class="headerText"> Hi there, music nerd. </h2>
        <h4 class="headerText"> If you're looking for a massive database where people like you review their favorite (or least favorite) music, then you've come to the right page... What are you waiting for? Read some reviews, or log in to write one yourself!</h4>
      </div>
    <?php } ?>


      <h2 class="header-text"> Click on an album to see the review </h2>

        <div class="inserted-text">
          <!-- check if review was inserted properly -->
          <?php if ($review_inserted) { ?>
            <p><strong>Thanks for your review!</strong></p>
          <?php } ?>
          <?php if ($review_insert_failed) { ?>
            <p class="feedback"><strong>Oops! Something went wrong with your review. Please try again.</strong></p>
          <?php } ?>
        </div>

        <div class="inserted-search">

          <?php if ($search_inserted) { ?>
            <p><strong>Search results:</strong></p>
          <?php } ?>
        </div>

      <!--  Media Gallery -->
      <section class="gallery">
        <?php
        if(!$filter_mode) {
          $records = exec_sql_query($db, $sql_select_query, $sql_select_params)->fetchAll();
        } else {
          $records = exec_sql_query($db, $filter_query, $filter_params)->fetchAll();
        }?>
            <?php if (count($records) > 0) { ?>
              <ul>
                <?php
                foreach ($records as $record) { ?>
                <?php $year = htmlspecialchars($record["year"]);?>
                  <li class="tile">
                    <div class="tile-right">
                        <a href="/album-reviews/review?<?php echo http_build_query(array('id' => $record['id'])); ?>">
                        <img src="/public/uploads/album-covers/<?php echo $record['id'] . '.' . $record['file_ext']; ?>" alt="<?php echo htmlspecialchars($record['album']);
                        ?>" />
                        </a>
                    </div>
                    <div class="tile-left">
                    <div class="tile-header">
                      <a href="/album-reviews/review?<?php echo http_build_query(array('id' => $record['album_id'])); ?>">
                        <h3><?php echo htmlspecialchars($record["album"]) . " (" . ($year) . ")"; ?></h3>
                      </a>
                    </div>
                      <h4 class="artist"><?php echo htmlspecialchars($record["artist"]); ?> </h4>
                      <h4 class="name"><?php echo "Review by ". htmlspecialchars($record["name"]); ?> </h4>
                      <h4 class="rating"><?php echo "Rating: " . htmlspecialchars($record["rating"]) . "/10"; ?> </h4>
                    </div>
                  </li>
                <?php } ?>
              </ul>
               <?php } else { ?>
               </section>
              <p>No reviews found.</p>
            <?php } ?>

    </section>

  </div>
  <!-- Search and Sort Section -->
  <div class="side-by-side">
    <h2 class="header-text"> Looking for something? </h2>

      <!-- Search -->
    <div class="search-div">
      <form action="/" method="get" novalidate>

          <h3 class="side-headers"> Search for artists and album names: </h3>
          <div id="searchFeedback" class="searchFeedback <?php echo $search_feedback_class; ?>">Please enter a search.</div>
          <input class="search" aria-label="Search" placeholder="Search" id="search" type="text" name="q" value="<?php echo htmlspecialchars($sticky_search); ?>" required />

        <button type="submit" class="search-button">Search</button>
      </form>
    </div>

    <!-- Sort -->
    <div class="sort">
      <h3 class="side-headers">Sort by: </h3> <div class="sort-options">
        <a class="<?php echo $sort_css_classes['high']; ?>" href="/?sort=new">Newer Albums</a>
        <a class="<?php echo $sort_css_classes['low']; ?>" href="/?sort=old">Older Albums</a>
        <a class="<?php echo $sort_css_classes['new']; ?>" href="/?sort=high">Highest Ratings</a>
        <a class="<?php echo $sort_css_classes['old']; ?>" href="/?sort=low">Lowest Ratings</a>
      </div>
    </div>

    <!-- Filter -->
    <div class="filter" >

      <h3 class="side-headers"> Filter by tags: </h3>
        <div class="tag-names">

          <?php if (!empty($current_tag_names[0]['tag_name'])) { ?>


            <?php
            foreach($current_tag_names as $current_tag_name) { ?>

              <?php
              $tag_name = htmlspecialchars($current_tag_name['tag_name']);
              $filter_url = $url . http_build_query(array('filter-tag' => htmlspecialchars($current_tag_name['tag_id'])));
              ?>
              <?php if (!is_null($current_tag_name['tag_name'])) { ?>
                <div class="tag-name"><a href="<?php echo($filter_url) ?>"> <?php echo($tag_name) ?> </a></div>
              <?php } ?>
            <?php }

          }  else { ?>

            <?php $current_tag_name['tag_name'] = 'hidden'; ?>

            <p> No tags found </p>

          <?php } ?>
        </div>
          <div class="all-reviews"><a href="/"> BACK TO ALL REVIEWS </a></div>

    </div>

  </div>
</div>

</body>
</html>
