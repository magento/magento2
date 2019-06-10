<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
// phpcs:ignore Magento2.Security.IncludeFile
include __DIR__ . '/authenticate.php';

// phpcs:ignore Magento2.Security.Superglobal
if (!empty($_POST['token']) && !empty($_POST['template'])) {
    // phpcs:ignore Magento2.Security.Superglobal
    if (authenticate(urldecode($_POST['token']))) {
        $varDir = '../../../../var/export/';
        // phpcs:ignore Magento2.Security.Superglobal
        $template = urldecode($_POST['template']);
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $fileList = scandir($varDir, SCANDIR_SORT_NONE);
        $files = [];

        foreach ($fileList as $fileName) {
            if (preg_match("`$template`", $fileName) === 1) {
                $filePath = $varDir . $fileName;
                $files[] = [
                    // phpcs:ignore Magento2.Functions.DiscouragedFunction
                    'content' => file_get_contents($filePath),
                    'name' => $fileName,
                    // phpcs:ignore Magento2.Functions.DiscouragedFunction
                    'date' => filectime($filePath),
                ];
            }
        }

        // phpcs:ignore Magento2.Security.LanguageConstruct, Magento2.Security.InsecureFunction
        echo serialize($files);
    } else {
        // phpcs:ignore Magento2.Security.LanguageConstruct
        echo "Command not unauthorized.";
    }
} else {
    // phpcs:ignore Magento2.Security.LanguageConstruct
    echo "'token' or 'template' parameter is not set.";
}
