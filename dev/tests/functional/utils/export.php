<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
include __DIR__ . '/authenticate.php';

if (!empty($_POST['token']) && !empty($_POST['template'])) {
    if (authenticate(urldecode($_POST['token']))) {
        $varDir = '../../../../var/';
        $template = urldecode($_POST['template']);
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
    } else {
        echo "Command not unauthorized.";
    }
} else {
    echo "'token' or 'template' parameter is not set.";
}
