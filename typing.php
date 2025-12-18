<?php
session_start();
require_once __DIR__ . "/typing/paragraphs.php";

$levels = [
  "easy" => 50,
  "medium" => 100,
  "hard" => 150,
  "hardcore" => 300,
];

$level = $_GET["level"] ?? "easy";
if (!isset($levels[$level])) $level = "easy";

$data = buildTextForLevel($level);
$paragraph = $data["text"];
$words = $data["words"];


// store in session if you ever want server validation later
$_SESSION["typing_level"] = $level;
$_SESSION["typing_text"] = $paragraph;
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="assets/style.css">
  <title>Text Typing</title>
</head>
<body>
  <?php include __DIR__ . "/partials/header.php"; ?>

  <main style="max-width:1000px;margin:20px auto;padding:0 12px;">
    <h1>Text Typing</h1>

    <form method="GET" style="margin-bottom:12px;">
      <label>Difficulty:</label>
      <select name="level" onchange="this.form.submit()">
        <option value="easy" <?= $level==="easy"?"selected":"" ?>>Easy (50 words)</option>
        <option value="medium" <?= $level==="medium"?"selected":"" ?>>Medium (100 words)</option>
        <option value="hard" <?= $level==="hard"?"selected":"" ?>>Hard (150 words)</option>
        <option value="hardcore" <?= $level==="hardcore"?"selected":"" ?>>HardCore (300 words)</option>
      </select>
      <button type="button" onclick="window.location.reload()">Restart</button>
    </form>

    <div style="border:1px solid #ccc;padding:12px;">
      <div>
        Time: <span id="time">00:00</span> |
        WPM: <span id="wpm">0</span> |
        Accuracy: <span id="acc">100</span>% |
        Correct: <span id="correct">0</span>/<?= count($words) ?>
      </div>

      <hr>

        <div id="promptBox" style="border:1px solid #ccc; padding:10px; height:180px; overflow-y:auto; line-height:2;">
            <div id="prompt"></div>
        </div>


      <p><input id="input" style="width:100%;padding:10px;" placeholder="Start typing... (timer starts on first key)"></p>

      <div style="margin-top:10px;">
        <input id="nickname" placeholder="Nickname" maxlength="20">
        <button id="saveBtn" type="button" disabled>Save result</button>
        <span id="msg"></span>
      </div>
    </div>

    <h2 style="margin-top:18px;">Leaderboard (<?= htmlspecialchars($level) ?>)</h2>
    <div id="leaderboard">Loading...</div>
  </main>

  <script>
    window.__WORDS__ = <?= json_encode($words, JSON_UNESCAPED_UNICODE) ?>;
    window.__LEVEL__ = <?= json_encode($level) ?>;
  </script>
  <script src="assets/typing.js"></script>
</body>
</html>
