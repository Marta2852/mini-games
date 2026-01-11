<?php
header("Content-Type: application/json; charset=utf-8");

$levels = ["easy","medium","hard"];
$file = __DIR__ . "/leaderboard.json";

function readBoard(string $file): array {
    if (!file_exists($file)) return [];
    $raw = file_get_contents($file);
    $data = json_decode($raw ?: "{}", true);
    return is_array($data) ? $data : [];
}

function writeBoard(string $file, array $data): void {
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    $mode = $_GET["mode"] ?? "";
    $level = $_GET["level"] ?? "easy";

    if ($mode !== "list" || !in_array($level, $levels, true)) {
        echo json_encode(["ok"=>false, "error"=>"Bad request"]);
        exit;
    }

    $board = readBoard($file);
    $items = $board[$level] ?? [];

    usort($items, fn($a, $b) => $a["seconds"] <=> $b["seconds"]);

    echo json_encode(["ok"=>true, "items"=>array_slice($items, 0, 10)], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $data = $_POST;
    if (empty($data)) exit;

    $seconds = (int)$data["time"];
    $m = str_pad(floor($seconds / 60), 2, "0", STR_PAD_LEFT);
    $s = str_pad($seconds % 60, 2, "0", STR_PAD_LEFT);
    $formatted_time = "$m:$s";

    $board = readBoard($file);

    $level = $data["level"];
    if (!isset($board[$level]) || !is_array($board[$level])) $board[$level] = [];

    $board[$level][] = [
      "nickname" => htmlspecialchars($data["nickname"]),
      "time" => $formatted_time,
      "seconds" => $seconds,
      "moves" => (int)$data["moves"],
      "accuracy" => (int)$data["accuracy"],
      "created_at" => date("c")
    ];

    usort($board[$level], fn($a,$b) => $a["seconds"] <=> $b["seconds"]);
    $board[$level] = array_slice($board[$level], 0, 10);

    writeBoard($file, $board);

    echo json_encode(["success" => true, "message"=>"Score saved!"]);
    exit;
}

echo json_encode(["ok"=>false, "error"=>"Method not allowed"]);
