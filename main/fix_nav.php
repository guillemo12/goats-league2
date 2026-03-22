<?php
$files = [
    'index.php', 'estadisticas.php', 'jugadores.php', 'pizarra.php', 
    'calendario.php', 'team.php', 'profile/index.php'
];

$dropdownHtml = '
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Equipos</a>
                        <ul class="dropdown-menu dropdown-menu-dark">
                            <?php 
                            if (!isset($navTeams)) {
                                $navTeams = $pdo->query("SELECT id, name FROM teams ORDER BY name ASC")->fetchAll();
                            }
                            foreach ($navTeams as $nT): 
                            ?>
                                <li><a class="dropdown-item" href="<?php echo (strpos($_SERVER[\'SCRIPT_NAME\'], \'profile/\') !== false) ? \'../\' : \'./\'; ?>team.php?id=<?php echo $nT[\'id\']; ?>"><?php echo htmlspecialchars($nT[\'name\']); ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </li>';

foreach ($files as $file) {
    if (!file_exists($file)) continue;
    $content = file_get_contents($file);
    
    // Remove existing dropdown variants
    $content = preg_replace('/<\?php\s+if\s*\(isset\(\$_SESSION\[[\'"]user(?:_id)?[\'"]\]\)\):\s*\?>\s*<li class="nav-item dropdown">.*?<\/li>\s*<\?php\s*endif;\s*\?>/is', '', $content);
    $content = preg_replace('/<li class="nav-item dropdown">\s*<a class="nav-link dropdown-toggle"[^>]*>\s*Equipos.*?<\/li>/is', '', $content);
    
    // Inject
    $content = preg_replace('/(<ul class="navbar-nav ms-auto align-items-center">)/i', '$1' . $dropdownHtml, $content, 1);
    
    file_put_contents($file, $content);
    echo "Updated $file\n";
}
echo "Done.\n";
?>
