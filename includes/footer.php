<footer>
  <?php if (file_exists("../styles/footer.css")) { ?>
    <link rel="stylesheet" href="../styles/footer.css">
  <?php } else { ?>
    <link rel="stylesheet" href="../../styles/footer.css">
  <?php } ?>
  <div class="footer">
    <div>
      <a href="#about">About Us</a>
      <a href="#contact">Contact Us</a>
      <a href="#terms">Terms</a>
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