<?php
function getTypingTop3(string $filePath): array {
    if (!file_exists($filePath)) return [];
    $raw = file_get_contents($filePath);
    $data = json_decode($raw ?: "{}", true);
    if (!is_array($data)) return [];

    $all = [];
    foreach ($data as $level => $items) {
        if (!is_array($items)) continue;
        foreach ($items as $it) {
            if (!is_array($it)) continue;
            $all[] = [
                "nickname" => (string)($it["nickname"] ?? "Unknown"),
                "level"    => (string)$level,
                "wpm"      => (int)($it["wpm"] ?? 0),
                "accuracy" => (int)($it["accuracy"] ?? 0),
                "time"     => (string)($it["time"] ?? "00:00"),
                "seconds"  => (int)($it["seconds"] ?? 999999),
            ];
        }
    }

    usort($all, function($a, $b) {
        return ($b["wpm"] <=> $a["wpm"])
            ?: ($b["accuracy"] <=> $a["accuracy"])
            ?: ($a["seconds"] <=> $b["seconds"]);
    });

    return array_slice($all, 0, 3);
}

$top3 = getTypingTop3(__DIR__ . "/typing/leaderboard.json");
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="assets/style.css">
  <title>Game Menu</title>
</head>
<body>
  <?php include __DIR__ . "/partials/header.php"; ?>

  <main style="max-width:1000px;margin:20px auto;padding:0 12px;">
    <h1>Game Menu</h1>
    <p>Pick a game. Index shows Top 3 highscores.</p>

    <section style="border:1px solid #ccc;padding:12px;margin-bottom:16px;">
      <h2>⌨️ Text Typing</h2>
      <p><a href="typing.php">Play Typing Game</a></p>

      <h3>Top 3 Highscores (overall)</h3>
      <?php if (count($top3) === 0): ?>
        <p>No scores yet. Go play and save one!</p>
      <?php else: ?>
        <ol>
          <?php foreach ($top3 as $row): ?>
            <li>
              <strong><?= htmlspecialchars($row["nickname"]) ?></strong>
              — <?= (int)$row["wpm"] ?> WPM,
              <?= (int)$row["accuracy"] ?>%,
              Level: <?= htmlspecialchars($row["level"]) ?>,
              Time: <?= htmlspecialchars($row["time"]) ?>
            </li>
          <?php endforeach; ?>
        </ol>
      <?php endif; ?>
    </section>

    <section style="border:1px solid #ccc;padding:12px;">
      <h2>🃏 Memory Cards</h2>
      <p><a href="memory.php">Open</a></p>
      <h3>Top 3 Highscores</h3>
      <p>Coming soon (partner’s game).</p>
    </section>
  </main>
</body>
</html>
