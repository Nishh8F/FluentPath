<?php
$html = file_get_contents('index.html');
preg_match_all('/<script.*?>(.*?)<\/script>/is', $html, $matches);
foreach($matches[1] as $i => $s) {
    $lines = explode("\n", $s);
    echo "Script $i Line 2: " . trim($lines[1] ?? '') . "\n";
}
