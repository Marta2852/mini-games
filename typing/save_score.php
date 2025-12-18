<?php
declare(strict_types=1);

header("Content-Type: application/json; charset=utf-8");

$levels = ["easy","medium","hard","hardcore"];
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

    usort($items, function($a, $b) {
        return ($b["wpm"] <=> $a["wpm"])
            ?: ($b["accuracy"] <=> $a["accuracy"])
            ?: ($a["seconds"] <=> $b["seconds"]);
    });

    echo json_encode(["ok"=>true, "items"=>array_slice($items, 0, 10)], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $input = json_decode(file_get_contents("php://input") ?: "{}", true);
    if (!is_array($input)) {
        echo json_encode(["ok"=>false, "error"=>"Invalid JSON"]);
        exit;
    }

    $nickname = trim((string)($input["nickname"] ?? ""));
    $level = (string)($input["level"] ?? "");
    $wpm = (int)($input["wpm"] ?? 0);
    $accuracy = (int)($input["accuracy"] ?? 0);
    $time = (string)($input["time"] ?? "00:00");
    $seconds = (int)($input["seconds"] ?? 0);

    if ($nickname === "" || !in_array($level, $levels, true)) {
        echo json_encode(["ok"=>false, "error"=>"Bad data"]);
        exit;
    }

    $nickname = mb_substr($nickname, 0, 20);
    $wpm = max(0, $wpm);
    $accuracy = max(0, min(100, $accuracy));
    $seconds = max(1, $seconds);

    $board = readBoard($file);
    if (!isset($board[$level]) || !is_array($board[$level])) $board[$level] = [];

    $board[$level][] = [
        "nickname" => $nickname,
        "wpm" => $wpm,
        "accuracy" => $accuracy,
        "time" => $time,
        "seconds" => $seconds,
        "created_at" => date("c"),
    ];

    writeBoard($file, $board);
    echo json_encode(["ok"=>true], JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode(["ok"=>false, "error"=>"Method not allowed"]);
