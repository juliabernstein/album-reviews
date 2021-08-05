<?php
include("includes/init.php");
$nav_home_class = "current_page";


// find which review to show
$review_id = (int)trim($_GET['id']);
$url = "/album-reviews/review?" . http_build_query(array('id' => $review_id));
$review_id = (int)trim($_GET['id']);

// edit, delete, delete tag, and add mode

$edit_mode = False;
$edit_authorization = False;

$delete_mode = False;
$delete_authorization = False;

$delete_tag_mode = False;
$delete_tag_authorization = False;

$add_tag_mode = False;
$add_tag_authorization = False;


// Feedback CSS classes
$rating_feedback_class = 'hidden';
$review_feedback_class = 'hidden';
$add_tag_feedback_class = 'hidden';

$folder = 'public/uploads/album-covers/';


if (is_user_logged_in()) {

  // if in editing mode
  if (isset($_GET['edit'])) {
    $edit_mode = True;

    // set review id to edit param value
    $review_id = (int)trim($_GET['edit']);
  }

  // if in delete mode
  if (isset($_GET['delete'])) {
    $delete_mode = True;

    // set review id to delete param value
    $review_id = (int)trim($_GET['delete']);
  }

  // if in delete tag mode
  if (isset($_GET['delete-tag'])) {
    $delete_tag_mode = True;

    // set id and delete-tag param values
    $review_id = (int)trim($_GET['id']);
    $tag_id = $_GET['delete-tag'];
  }

  //  if in add tag mode
  if (isset($_POST["add-tag"])) {
      $add_tag_name = strtolower(trim($_POST['add-tag-name']));
      $add_tag_name_formatted = str_replace('', '_', $add_tag_name);

      // if new tag is not empty
      if (!empty($add_tag_name)) {
        $add_tag_mode = True;
        $add_tag_feedback_class = 'hidden';

        // check to see if added tag exists
        $check_query = "SELECT * FROM tags where tag_name =:add_tag_name_formatted;";
        $check_params = array(
        ':add_tag_name_formatted' => $add_tag_name_formatted
        );

        $check = exec_sql_query($db, $check_query, $check_params)->fetchAll();
      } else {
       $add_tag_feedback_class = '';
      }
  }


}
  // if in add tag mode
  if ($add_tag_mode) {

    // if the tag exists, only add it to the album_tags table
    if ($check) {
      $tag_id = $check[0]['id'];
      $album_tags_query = "INSERT INTO album_tags (album_id, tag_id) VALUES (:album_id, :tag_id);";
      $album_tags_params = array(
        ':album_id'=>$review_id,
        ':tag_id'=>$tag_id
      );
      $add_to_album_tags = exec_sql_query($db, $album_tags_query, $album_tags_params);
    }

    // if the tag does not exist, add to tags table and album_tags table
    else {
      $add_new_tag_query = "INSERT INTO tags (tag_name) VALUES (:add_tag_name_formatted);";
      $add_new_tag_params = array (
        ':add_tag_name_formatted'=> $add_tag_name_formatted
      );
      $add_new_tag = exec_sql_query($db, $add_new_tag_query, $add_new_tag_params);

      $tag_id = $db -> lastInsertId('id');

      // connect tag to album in album_tags

      $album_tags_new_sql = "INSERT INTO album_tags (album_id, tag_id) VALUES (:album_id, :tag_id);";
      $album_tags_new_params = array(
        ':album_id'=> $review_id,
        ':tag_id'=> $tag_id
      );
      $album_tags_new = exec_sql_query($db, $album_tags_new_sql, $album_tags_new_params);
    }

  }

    // if in delete tag mode
    if ($delete_tag_mode) {

      // query to delete tag from album_tags
      $records = exec_sql_query($db, "DELETE FROM album_tags WHERE album_id = :album_id AND tag_id = :tag_id;",
      array(
        ":album_id" => $review_id,
        ":tag_id" => $tag_id
        )

      )->fetchAll();

    }

  // find the record
  if ($review_id) {
      $records = exec_sql_query(
        $db,
        "SELECT * FROM albums LEFT OUTER JOIN album_tags ON albums.id = album_tags.album_id LEFT OUTER JOIN tags ON tags.id= tag_id WHERE albums.id = :id;",
        array(':id' => $review_id)
      )->fetchAll();
      if (count($records) > 0) {
        $album_review = $records[0];
      } else {
        $album_review = NULL;
      }

    // finds all tags associated with album
    $current_tag_names = exec_sql_query(
      $db,
      "SELECT DISTINCT tag_name, tag_id FROM albums LEFT OUTER JOIN album_tags ON albums.id = album_tags.album_id LEFT OUTER JOIN tags ON tags.id= tag_id WHERE albums.id = :id;",
      array(':id' => $review_id)
    )->fetchAll();

  }


// if there is a review
if ($album_review) {

  error_log($current_user['id'], $album_review['user_id']);

    // if the user is the author of the review, allow certain authorizations
    if ($current_user['id']==$album_review['user_id']){
      $edit_authorization = True;
      $delete_authorization = True;
      $delete_tag_authorization = True;
    }

    // if the user is an admin, allow certain authorizations
    if (is_user_member_of($db, 1)) {
      $delete_authorization = True;
      $delete_tag_authorization = True;
    }

  // if user has permission to edit
  if($edit_authorization) {

    // if review was edited
    if (isset($_POST['edit-review'])) {

      $rating = trim($_POST['rating']); // untrusted
      $review_text = trim($_POST['review']); // untrusted

      $form_valid = TRUE;

      // check if rating/review are empty

      if (empty($rating)) {
        $form_valid = FALSE;
        $rating_feedback_class = '';
      } else {
        $rating_feedback_class = 'hidden';
      }

      if (empty($review_text)) {
        $form_valid = FALSE;
        $review_feedback_class = '';
      } else {
        $review_feedback_class = 'hidden';
      }

      // if form is valid, update entry
      if ($form_valid) {
        exec_sql_query(
          $db,
          "UPDATE albums SET review = :review, rating = :rating WHERE (id = :id);",
          array(
            'id' => $review_id,
            'review' => $review_text,
            'rating' => $rating
          )
        );

        // get updated review
        $records = exec_sql_query(
          $db,
          "SELECT * FROM albums WHERE id = :id;",
          array(':id' => $review_id)
        )->fetchAll();
        $album_review = $records[0];
      } else {
        $sticky_rating = $rating; // tainted
        $sticky_review_text = $review_text; // tainted
        $feedback_mode = True;
      }

    }

  }


  // review information
  $url = "/album-reviews/review?" . http_build_query(array('id' => $review_id));
  $edit_url = "/album-reviews/review?" . http_build_query(array('id' => $review_id)) . '&' . http_build_query(array('edit' => $review_id));
  $delete_url = "/album-reviews/review?" . http_build_query(array('id' => $review_id)) . '&' . http_build_query(array('delete' => $review_id));

}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <title>Album Review</title>

  <link rel="stylesheet" type="text/css" href="/public/styles/site.css" media="all" />
</head>

<body>
<!-- Body -->
  <?php include("includes/header.php"); ?>

  <div class="body-div">

    <div class="side-by-side">
      <div class="review-side">
        <div class="back-to-gallery"><a href="/">Back to album gallery</a></div>

        <!-- Tags section -->

        <h2> Tags </h2>

        <?php if ((count($current_tag_names)>0) && ($current_tag_names[0]['tag_id'])) { ?>

          <?php
          foreach($current_tag_names as $current_tag_name) { ?>

            <?php
            $tag_name = htmlspecialchars($current_tag_name['tag_name']);
            $delete_tag_url = $url . '&' . http_build_query(array('delete-tag' => htmlspecialchars($current_tag_name['tag_id'])));
            ?>


            <?php if($delete_tag_authorization) { ?>
              <div class="tag-name"> <?php echo($tag_name) ?> | <a href="<?php echo($delete_tag_url)?>">x</a> </div>
            <?php } else { ?>
              <div class="tag-name"> <?php echo($tag_name) ?> </div>
            <?php } ?>

          <?php } ?>
        <?php } else { ?>

          <p> No tags found. </p>

        <?php } ?>

      </div>

    </div>


    <div class="side-by-side-middle">

      <?php if ($album_review) { ?>

        <!-- if in edit mode, show edit form -->
        <?php if ($edit_mode || $feedback_mode) { ?>
            <form class="edit" action="<?php echo $url; ?>" method="post" novalidate />

              <div id="reviewFeedback" class="editFeedback <?php echo $review_feedback_class;?>"> Please leave a review.</div>
              <label class="label-edit"for="request-review"> Your review: </label>
              <div class="input-area-review">
                  <?php if ($feedback_mode) { ?>
                    <textarea name="review" class="input-area-review" id="request-review"><?php echo htmlspecialchars($sticky_review_text);?></textarea>
                  <?php } else { ?>
                    <textarea name="review" class="input-area-review" id="request-review"><?php echo htmlspecialchars($album_review['review']);?></textarea>
                  <?php } ?>
              </div>

              <div id="ratingFeedback" class="editFeedback <?php echo $rating_feedback_class; ?>">Please pick a rating.</div>
              <label class="label-edit" for="request-rating"> Rating (out of 10): </label>
              <div class="input-area-review">
                  <?php if ($feedback_mode) { ?>
                    <input type="number" class="input-area-review" name="rating" value="<?php echo htmlspecialchars($sticky_rating); ?>"  />
                  <?php } else { ?>
                    <input type="number" class="input-area-review" name="rating" value="<?php echo htmlspecialchars($album_review['rating']); ?>"  />
                  <?php } ?>
              </div>

              <div class="submit-button-edit">
                  <!-- <input id="submit-button-edit" type="submit" value="Submit" name="submit"/> -->
                  <button class="submit-button-edit" type="submit" name="edit-review">Submit</button>
              </div>
            </form>
            <!-- if in delete mode -->
          <?php
          } else if ($delete_mode) { ?>

            <?php $filename = $folder . $review_id . '.' . $album_review['file_ext'];
          ?>
            <!-- delete entry from albums database-->
            <?php
            $records = exec_sql_query(
              $db,
            "DELETE FROM albums WHERE id = :review_id;",
              array(':review_id' => $review_id)
            )->fetchAll();

              // remove associated file upload
            unlink($filename);

            ?>

            <h2 class="deleted-review"> This review has been deleted. </h2>

          <?php
          // if not in any mode, show review
          } else { ?>

            <h3 class="review-title">
                <?php echo htmlspecialchars($album_review['album']); ?>
            </h3>

            <h3 class="review-artist">
                <?php echo htmlspecialchars($album_review['artist']); ?>
            </h3>

            <img src="/public/uploads/album-covers/<?php echo $review_id . '.' . $album_review['file_ext']; ?>" class = "review-page-image" alt="<?php echo htmlspecialchars($album_review['album']); ?>" />


            <div class="cite"><cite>
               <a href="<?php echo htmlspecialchars($album_review['source']); ?>"><?php echo htmlspecialchars($album_review['source']); ?></a>
            </cite></div>
            <div class=review-box>

              <h3 class="review-text"> Rating:
                <?php echo htmlspecialchars($album_review['rating']); ?>/10
              </h3>

              <p class="review-text"> Review by <?php echo htmlspecialchars($album_review['name']); ?> </p>

              <p class="review-text">
                <?php echo htmlspecialchars($album_review['review']); ?>
              <p>

                <!-- only allow edits if logged in user is the same as the review author -->
              <?php if ((is_user_member_of($db, 1)) || ($current_user['id']==$album_review['user_id'])) { ?> <div class="edit-delete">
                <p> <a href="<?php echo $edit_url; ?>">Edit Review</a> or <a href="<?php echo $delete_url; ?>">Delete Review </a> </p> </div>
              <?php } ?>
            </div>

          <?php
          } ?>

        <?php
        } else { ?>
            <p><strong>The review you were looking for does not exist.</strong> Try locating the review from the <a href="/">album gallery</a>.</strong></p>
          <?php
        } ?>
    </div>
    <div class="side-by-side">


      <?php if(!is_user_logged_in()) {?>

        <h3 class="log-in-tags"> Log in to add/delete tags </h3>
        <?php echo_login_form($url, $session_messages); ?>
      <?php } else { ?>

        <form action= "<?php echo($url); ?>" method="post" novalidate>

          <label for="add-tag-name" class="log-in-tags"> Create/add a new tag: </label>
          <div id="add-tag" class="reviewFeedback <?php echo $add_tag_feedback_class; ?>">Please provide a tag</div>
          <input class="input-area"  placeholder="Enter a new tag"  type="text" id="add-tag-name" name="add-tag-name" required/>


          <div class=submit-tag-button>
            <button class="submit-tag-button" type="submit" name="add-tag">Add Tag </button>
          </div>

        </form>
      <?php } ?>

    </div>
  </div>

</body>
</html>
