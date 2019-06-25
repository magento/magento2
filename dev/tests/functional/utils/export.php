<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// phpcs:ignore Magento2.Security.Superglobal
if (!isset($_GET['template'])) {
    // phpcs:ignore Magento2.Exceptions.DirectThrow
    throw new \InvalidArgumentException('Argument "template" must be set.');
}

$varDir = '../../../../var/export/';
// phpcs:ignore Magento2.Security.Superglobal
$template = urldecode($_GET['template']);
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
