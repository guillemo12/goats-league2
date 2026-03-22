<?php
$files = [
    'index.php', 'estadisticas.php', 'jugadores.php', 'pizarra.php', 
    'calendario.php', 'team.php', 'profile/index.php'
];

foreach ($files as $file) {
    if (!file_exists($file)) continue;
    $content = file_get_contents($file);
    
    // Avoid double inclusion
    if (strpos($content, 'mercado.php') === false) {
        $replacement = '<li class="nav-item">'."\n".'                        <a class="nav-link" href="jugadores.php">Jugadores</a>'."\n".'                    </li>'."\n".'                    <li class="nav-item">'."\n".'                        <a class="nav-link" href="mercado.php">Mercado</a>'."\n".'                    </li>';
        
        $content = preg_replace('/<li class="nav-item">\s*<a class="nav-link[^>]*" href="jugadores.php">Jugadores<\/a>\s*<\/li>/is', $replacement, $content);
        
        // Ensure paths for profile are fixed
        if ($file === 'profile/index.php') {
            $content = str_replace('href="mercado.php"', 'href="../mercado.php"', $content);
        }
        
        file_put_contents($file, $content);
        echo "Updated $file\n";
    }
}
echo "Done.\n";
?>
