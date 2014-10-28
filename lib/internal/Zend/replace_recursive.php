<?php

replace_recursive('.');

function replace_recursive($dir)
{
    $files = glob($dir.'/*');
    foreach ($files as $file) {
        if ($file=='.' || $file=='..') {
            continue;
        }
        if (is_dir($file)) {
            replace_recursive($file);
            continue;
        }
        if (substr($file, -4)!=='.php') {
            continue;
        }

        if (false !== strpos($file, 'replace_recursive.php')) {
            continue;
        }

        $orig = file_get_contents($file);

        $replaced = preg_replace("/([^#])require_once/", "\$1#require_once", $orig);

        if (strpos($file, 'Locale/Math.php')!==false) {
            $replaced = str_replace(
                "#require_once 'Zend/Locale/Math/PhpMath.php';",
                "require_once 'Zend/Locale/Math/PhpMath.php';",
                $replaced);
        }

        if (strcmp($orig, $replaced)===0) {
            continue;
        }

        $fp = fopen($file, 'w');
        fwrite($fp, $replaced);
        fclose($fp);

        echo "Changed '$file'\n";
    }
}
