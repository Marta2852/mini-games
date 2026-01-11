<?php
$levels = [
  "easy" => [2, 2],
  "medium" => [3, 4],
  "hard" => [4, 5],
];

$level = "easy";
if (isset($_GET["level"]) && isset($levels[$_GET["level"]])) $level = $_GET["level"];

[$rows, $cols] = $levels[$level];
$totalCards = $rows * $cols;
$pairs = $totalCards / 2;
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="assets/style.css">
  <title>Memory Cards</title>
</head>
<body>

<?php include __DIR__ . "/partials/header.php"; ?>

<main style="max-width:1000px;margin:20px auto;padding:0 12px;">
  <h1>Memory Cards</h1>

<form style="margin-bottom:12px;">
      <label>Difficulty:</label>
      <select name="level">
        <option value="easy" <?= $level==="easy"?"selected":"" ?>>Easy (2×2)</option>
        <option value="medium" <?= $level==="medium"?"selected":"" ?>>Medium (3×4)</option>
        <option value="hard" <?= $level==="hard"?"selected":"" ?>>Hard (4×5)</option>
      </select>
      <button type="button" onclick="resetGame()">Restart</button>
  </form>

  <!-- GAME BOX -->
  <div style="border:1px solid #ccc;padding:12px;">
    
    <!-- Stats row -->
    <div>
      Time: <span id="time">00:00</span> |
      Moves: <span id="moves">0</span> |
      Accuracy: <span id="acc">100</span>% |
      Pairs: <span id="pairs">0</span>/<?= $pairs ?>
    </div>

    <hr>

    <!-- Game Grid -->
    <div id="game" class="memory-grid" style="--cols:<?= $cols ?>;"></div>

    <!-- Save Result -->
    <div style="margin-top:10px;">
      <input id="nickname" placeholder="Nickname" maxlength="20">
      <button id="saveBtn" disabled>Save result</button>
      <span id="msg" style="margin-left:10px;color:green;"></span>
    </div>

  </div> <!-- end game box -->

  <!-- Leaderboard -->
  <h2 style="margin-top:18px;">Leaderboard (<?= htmlspecialchars($level) ?>)</h2>
  <div style="padding:10px;border:1px solid #ccc;">
    <?php
    $leaderboardFile = __DIR__ . "/memory/leaderboard.json";
    $board = file_exists($leaderboardFile) ? json_decode(file_get_contents($leaderboardFile), true) : [];
    $items = $board[$level] ?? [];

    usort($items, fn($a, $b) => $a["seconds"] <=> $b["seconds"]);

    if (empty($items)) {
        echo "No scores yet.";
    } else {
        echo "<ol>";
        foreach (array_slice($items, 0, 10) as $row) {
            $nickname = htmlspecialchars($row["nickname"] ?? "");
            $time = htmlspecialchars($row["time"] ?? "");
            $moves = $row["moves"] ?? 0;
            $accuracy = $row["accuracy"] ?? 0;
            echo "<li><strong>$nickname</strong> — $time, $moves moves, $accuracy%</li>";
        }
        echo "</ol>";
    }
    ?>
  </div>

</main>

<script>
  // Initial level
  document.querySelector('select[name="level"]').value = "easy";
</script>
<script src="assets/memory.js"></script>
</body>
</html>
