<?php

function getParagraphs(): array {
    return [
        "Typing speed is an important skill in the modern digital world. Students use it every day for school assignments coding projects and communication. Practicing regularly helps improve both accuracy and confidence while typing.",

        "Programming requires focus patience and logical thinking. When writing code developers must pay attention to small details because even a tiny mistake can cause errors in the program. Typing accurately saves time and reduces frustration.",

        "Learning new skills takes time and effort. Mistakes are a normal part of the learning process and should not discourage progress. With practice determination and consistency anyone can improve their abilities.",

        "Technology continues to evolve and influence everyday life. Computers smartphones and the internet have changed how people learn work and communicate. Understanding digital tools has become an essential skill.",

        "Games can be both entertaining and educational. Typing games help students improve keyboard skills while making practice more enjoyable. Combining learning with fun often leads to better results."
    ];
}

function levelWordTarget(string $level): int {
    return match ($level) {
        "easy" => 50,
        "medium" => 100,
        "hard" => 150,
        "hardcore" => 300,
        default => 50
    };
}

function splitWords(string $text): array {
    return preg_split('/\s+/', trim($text), -1, PREG_SPLIT_NO_EMPTY);
}

// ✅ returns ["text" => "...", "words" => [...], "target" => N]
function buildTextForLevel(string $level): array {
    $target = levelWordTarget($level);
    $paragraphs = getParagraphs();

    $words = [];
    $i = 0;

    // Keep adding paragraphs until we have at least $target words
    while (count($words) < $target) {
        $p = $paragraphs[$i % count($paragraphs)];
        $words = array_merge($words, splitWords($p));
        $i++;
    }

    // Cut to exactly the target number of words
    $words = array_slice($words, 0, $target);

    return [
        "target" => $target,
        "words" => $words,
        "text" => implode(" ", $words),
    ];
}
