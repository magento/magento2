<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

if (!isset($_GET['template'])) {
    throw new \InvalidArgumentException('Argument "template" must be set.');
}

$varDir = '../../../../var/';
$template = urldecode($_GET['template']);
$fileList = scandir($varDir, SCANDIR_SORT_NONE);
$files = [];

foreach ($fileList as $fileName) {
    if (preg_match("`$template`", $fileName) === 1) {
        $filePath = $varDir . $fileName;
        $files[] = [
            'content' => file_get_contents($filePath),
            'name' => $fileName,
            'date' => filectime($filePath),
        ];
    }
}

echo serialize($files);
