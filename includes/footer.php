<footer>
  <?php if (file_exists("../styles/footer.css")) { ?>
    <link rel="stylesheet" href="../styles/footer.css">
  <?php } else { ?>
    <link rel="stylesheet" href="../../styles/footer.css">
  <?php } ?>
  <div class="footer">
    <div>
      <a href="../public/aboutus.php">About Us</a>
      <a href="../public/contactus.php">Contact Us</a>
      <a href="../public/term.php">Terms</a>
    </div>
    <hr>
    <div>
      &copy; 2024 Mathy, Inc.
    </div>
  </div>
  <?php
  if ($conn->ping()) {
    $conn->close();
  }
  ?>
</footer>
