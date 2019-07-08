<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

require_once dirname(__FILE__) . '/' . 'bootstrap.php';

deleteDirectory(MTF_BP . '/generated');

// Generate moduleSequence.json file
generateModuleSequence();
// Generate factories for old end-to-end tests
$objectManager->create(\Magento\Mtf\Util\Generate\Factory::class)->launch();

$generatorPool = $objectManager->get('Magento\Mtf\Util\Generate\Pool');
foreach ($generatorPool->getGenerators() as $generator) {
    if (!$generator instanceof \Magento\Mtf\Util\Generate\LauncherInterface) {
        throw new \InvalidArgumentException(
            'Generator ' . get_class($generator) . ' should implement LauncherInterface'
        );
    }
    $generator->launch();
}

\Magento\Mtf\Util\Generate\GenerateResult::displayResults();


function deleteDirectory($dir)
{
    if (!file_exists($dir)) {
        return true;
    }
    if (!is_dir($dir)) {
        return unlink($dir);
    }
    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') {
            continue;
        }
        if (!deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
            return false;
        }
    }
    return rmdir($dir);
}

function generateModuleSequence()
{
    require_once "generate/moduleSequence.php";
}
