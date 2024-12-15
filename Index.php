<?php
// Load the playlist
$playlist = file("playlist.m3u", FILE_IGNORE_NEW_LINES);

// Handle remove request
if (isset($_GET['remove'])) {
    $lineIndex = (int)$_GET['remove'];
    unset($playlist[$lineIndex]);
    file_put_contents("playlist.m3u", implode("\n", $playlist));
    header("Location: index.php");
    exit();
}

// Handle download request with custom filename
if (isset($_GET['download'])) {
    $filename = isset($_GET['filename']) ? $_GET['filename'] : 'playlist.m3u';
    // Ensure the filename ends with .m3u
    $filename = pathinfo($filename, PATHINFO_EXTENSION) ? $filename : $filename . '.m3u';
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
    header('Content-Length: ' . filesize('playlist.m3u'));
    readfile('playlist.m3u');
    exit();
}

// Handle Reset Request
if (isset($_POST['reset'])) {
    file_put_contents('playlist.m3u', ''); // Clear the playlist file
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>M3U Playlist Creator</title>
  <style>
    /* Base Styles */
    body {
      background-color: #121212;
      color: #e0e0e0;
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 0;
    }
    .container {
      width: 90%;
      max-width: 800px;
      margin: 20px auto;
      background-color: #1e1e1e;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
    }
    h1 {
      text-align: center;
      margin-bottom: 20px;
      font-size: 2rem;
    }
    .input-group {
      margin-bottom: 15px;
    }
    .input-group label {
      display: block;
      font-weight: bold;
      margin-bottom: 5px;
    }
    .input-group input {
      width: 100%;
      padding: 10px;
      border: none;
      border-radius: 5px;
      background-color: #2e2e2e;
      color: #fff;
    }
    .btn {
      display: inline-block;
      padding: 10px 15px;
      background-color: #0078d7;
      color: #fff;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      margin-top: 10px;
    }
    .btn:hover {
      background-color: #005bb5;
    }
    .clear-btn {
      background-color: red;
      margin-top: 20px;
    }
    .clear-btn:hover {
      background-color: darkred;
    }
    /* Playlist Table Styles */
    .playlist-table {
      margin-top: 20px;
      border-collapse: collapse;
      width: 100%;
      overflow-x: auto; /* Ensure scroll on mobile */
    }
    .playlist-table th, .playlist-table td {
      padding: 10px;
      text-align: left;
      border: 1px solid #444;
    }
    .edit-btn, .remove-btn {
      background-color: #f39c12;
      color: #fff;
      padding: 5px 10px;
      border: none;
      cursor: pointer;
      border-radius: 5px;
    }
    .remove-btn {
      background-color: #e74c3c;
    }
    .edit-btn:hover, .remove-btn:hover {
      background-color: #d35400;
    }
    footer {
      text-align: center;
      padding: 10px;
      background-color: #1e1e1e;
      color: #fff;
      margin-top: 30px;
    }
    footer a {
      color: #f1c40f;
      text-decoration: none;
    }
    footer a:hover {
      text-decoration: underline;
    }
    /* Responsive Styles */
    @media (max-width: 768px) {
      .container {
        padding: 15px;
      }
      h1 {
        font-size: 1.5rem;
      }
      .playlist-table th, .playlist-table td {
        padding: 8px;
        font-size: 12px;
      }
      .btn, .clear-btn {
        font-size: 14px;
        padding: 8px 12px;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <h1>M3U Playlist Creator</h1>

    <!-- Form to Add Channel -->
    <form method="POST" action="process.php">
      <div class="input-group">
        <label for="group_title">Group Title</label>
        <input type="text" id="group_title" name="group_title" required>
      </div>
      <div class="input-group">
        <label for="logo_url">Channel Logo URL</label>
        <input type="url" id="logo_url" name="logo_url" required>
      </div>
      <div class="input-group">
        <label for="channel_name">Channel Name</label>
        <input type="text" id="channel_name" name="channel_name" required>
      </div>
      <div class="input-group">
        <label for="channel_url">Channel URL</label>
        <input type="url" id="channel_url" name="channel_url" required>
      </div>
      <button type="submit" class="btn">Add Channel</button>
    </form>

    <!-- Playlist Table with Edit and Remove Options -->
    <h3>Existing Channels</h3>
    <table class="playlist-table">
      <thead>
        <tr>
          <th>Group Title</th>
          <th>Channel Name</th>
          <th>Channel URL</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($playlist as $index => $line) {
          if (strpos($line, '#EXTINF') === 0) {
            // Extract channel details
            preg_match('/group-title="([^"]+)"/', $line, $groupTitleMatches);
            preg_match('/tvg-logo="([^"]+)"/', $line, $logoUrlMatches);
            preg_match('/, (.+)/', $line, $channelNameMatches);
            $groupTitle = $groupTitleMatches[1] ?? '';
            $channelName = $channelNameMatches[1] ?? '';
            $channelUrl = $playlist[$index + 1] ?? '';
        ?>
          <tr>
            <td><?= htmlspecialchars($groupTitle) ?></td>
            <td><?= htmlspecialchars($channelName) ?></td>
            <td><?= htmlspecialchars($channelUrl) ?></td>
            <td>
              <form method="GET" action="edit.php" style="display:inline;">
                <input type="hidden" name="line" value="<?= $index ?>">
                <button type="submit" class="edit-btn">Edit</button>
              </form>
              <form method="GET" action="index.php" style="display:inline;">
                <input type="hidden" name="remove" value="<?= $index ?>">
                <button type="submit" class="remove-btn">Remove</button>
              </form>
            </td>
          </tr>
        <?php }} ?>
      </tbody>
    </table>

    <!-- Reset Form Button -->
    <form method="POST" action="index.php">
      <button type="submit" name="reset" class="clear-btn">Reset All Channels</button>
    </form>

    <!-- Download Playlist with Custom Filename -->
    <h3>Download Playlist</h3>
    <form method="GET" action="index.php">
      <input type="text" name="filename" placeholder="Enter custom filename" required>
      <button type="submit" class="btn" name="download" value="1">Download Playlist</button>
    </form>
  </div>

  <footer>
    <p>Created by <a href="https://t.me/professor906" target="_blank">Professor</a></p>
    <p>&copy; 2024 All rights reserved.</p>
  </footer>
</body>
</html>
