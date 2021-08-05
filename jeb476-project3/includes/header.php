<?php $nav_home_class = "current_page";
?>
<header>
  <div>
    <h1 class="title"> ALBUM REVIEW DATABASE </h1>
  </div>
  <div>
    <h4 class="subheader"> By Julia Bernstein</h4>
  </div>


  <div>
    <nav>
      <ul>
        <li class="<?php echo $nav_home_class; ?>"><a href="/">home</a></li>
        <?php if (is_user_logged_in()) { ?>
          <li id="nav-logout" ><a href="<?php echo logout_url(); ?>">sign out</a></li>
        <?php } ?>
      </ul>
    </nav>
  </div>

</header>
