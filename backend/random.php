<?php

$rooms = json_decode(file_get_contents(__DIR__ . "/rooms.json"), true);
$all = array_merge(...array_values($rooms));
shuffle($all);

$toRemove = array_slice($all, 0, rand(10, 40));
foreach ($rooms as $floor => &$floorRooms) {
    $floorRooms = array_values(array_diff($floorRooms, $toRemove));
}

file_put_contents(__DIR__ . "/rooms.json", json_encode($rooms));
echo json_encode(["removed" => $toRemove]);