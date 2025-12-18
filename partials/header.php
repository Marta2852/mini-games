<?php
$current = basename($_SERVER["PHP_SELF"]);
function active($file, $current) { return $file === $current ? 'style="font-weight:bold;"' : ''; }
?>
<header style="padding:12px;border-bottom:1px solid #ccc;display:flex;justify-content:space-between;align-items:center;">
  <div><strong>🎮 Mini Games</strong></div>

  <nav style="display:flex;gap:12px;">
    <a <?= active('index.php',$current) ?> href="index.php">Game Menu</a>
    <a <?= active('typing.php',$current) ?> href="typing.php">Text Typing</a>
    <a <?= active('memory.php',$current) ?> href="memory.php">Memory Cards</a>
  </nav>
</header>
