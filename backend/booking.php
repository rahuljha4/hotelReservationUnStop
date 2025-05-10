<?php

function saveRooms(array $data): void
{
    file_put_contents(__DIR__ . "/rooms.json", json_encode($data));
}

function getRoomFloor(int $room): int
{
    return (int) floor($room / 100);
}

function travelTime(array $rooms): int
{
    $floors = array_map('getRoomFloor', $rooms);
    $minFloor = min($floors);
    $maxFloor = max($floors);
    $vertical = ($maxFloor - $minFloor) * 2;

    $horizontal = 0;
    if (count(array_unique($floors)) === 1) {
        sort($rooms);
        $horizontal = end($rooms) % 100 - $rooms[0] % 100;
    }

    return $vertical + $horizontal;
}

function combination(array $arr, int $size): array
{
    $results = [];

    generate($arr, [], 0, $size, $results);
    return $results;
}

function generate(array $arr, array $current, int $start, int $size, array &$results)
{
    if (count($current) === $size) {
        $results[] = $current;
        return;
    }

    for ($i = $start; $i < count($arr); $i++) {
        generate($arr, array_merge($current, [$arr[$i]]), $i + 1, $size, $results);
    }
}

function findBestRooms(array $rooms, int $numRooms): array
{
    $available = [];
    foreach ($rooms as $floorRooms) {
        if (count($floorRooms) >= $numRooms) {
            for ($i = 0; $i <= count($floorRooms) - $numRooms; $i++) {
                $slice = array_slice($floorRooms, $i, $numRooms);
                $available[] = $slice;
            }
        }
    }

    if (empty($available)) {
        $flat = array_merge(...array_values($rooms));
        $combos = combination($flat, $numRooms);
        foreach ($combos as $combo) {
            $available[] = $combo;
        }
    }

    usort($available, fn($a, $b) => travelTime($a) <=> travelTime($b));

    return $available[0] ?? [];
}

// Booking logic
$requestedRooms = (int) ($_POST["rooms"] ?? 1);
if ($requestedRooms < 1 || $requestedRooms > 5) {
    echo json_encode(["error" => "Invalid number of rooms"]);
    exit;
}

$rooms = json_decode(file_get_contents(__DIR__ . "/rooms.json"), true);
$bestRooms = findBestRooms($rooms, $requestedRooms);

// Remove booked
foreach ($rooms as $floor => &$floorRooms) {
    $floorRooms = array_values(array_diff($floorRooms, $bestRooms));
}

saveRooms($rooms);

echo json_encode([
    "booked" => $bestRooms,
    "travel_time" => travelTime($bestRooms)
]);