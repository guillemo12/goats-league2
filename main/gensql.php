<?php
$teams = [
    "Chepari FC" => ["capitan" => ["Manuel", "MasterChepari2024"], "jugadores" => ["David Zhang", "Samuelillo", "Huiyi", "Cfy", "Pablo wu", "Stiven", "Samuel", "Lin Bai", "Dani", "Papu"], "pass_jug" => "ChepariTeam2024"],
    "AnosEnjoyers" => ["capitan" => ["Cristobal", "MasterAnos2024"], "jugadores" => ["David Zheng", "Antonio", "Quesada", "Angel pablos", "Álvaro Chino", "Edu", "Josemi", "Antonio", "Pepe", "Fali", "Jesus"], "pass_jug" => "AnosTeam2024"],
    "Los Leones de la Macarena" => ["capitan" => ["Correa", "MasterLeones2024"], "jugadores" => ["Trenbala", "Esteban", "Diego Vicente", "Vitolo", "Álvaro Guerra", "Sebas", "Pablo", "Sergio", "Beastmort", "Moi"], "pass_jug" => "LeonesTeam2024"],
    "Churrita FC" => ["capitan" => ["Benmggy", "MasterChurrita2024"], "jugadores" => ["haochen", "david san jose", "Dani haochen", "Manu mao", "Jose wang", "Marco", "socio", "Mei hao xiang", "Adrian", "Josepablo"], "pass_jug" => "ChurritaTeam2024"]
];

foreach ($teams as $tName => $data) {
    echo "-- Equipo: $tName\n";
    $capName = $data['capitan'][0];
    $capHash = password_hash($data['capitan'][1], PASSWORD_DEFAULT);
    echo "INSERT INTO users (username, password, role, team_id) VALUES ('$capName', '$capHash', 'capitan', (SELECT id FROM teams WHERE name LIKE '%$tName%' LIMIT 1));\n";

    $jugHash = password_hash($data['pass_jug'], PASSWORD_DEFAULT);
    foreach ($data['jugadores'] as $j) {
        echo "INSERT INTO users (username, password, role, team_id) VALUES ('$j', '$jugHash', 'user', (SELECT id FROM teams WHERE name LIKE '%$tName%' LIMIT 1));\n";
    }
    echo "\n";
}
?>
