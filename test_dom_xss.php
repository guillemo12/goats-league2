<?php
// Simple script to test if php syntax is correct on main files.
exec('php -l main/tratos.php', $output, $return_var);
if ($return_var !== 0) {
    echo "Syntax error in main/tratos.php\n";
    exit(1);
}
exec('php -l main/match.php', $output2, $return_var2);
if ($return_var2 !== 0) {
    echo "Syntax error in main/match.php\n";
    exit(1);
}
echo "Syntax OK\n";
