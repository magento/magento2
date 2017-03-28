<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

require_once dirname(__FILE__) . '/' . 'bootstrap.php';

// Generate fixtures
$magentoObjectManagerFactory = \Magento\Framework\App\Bootstrap::createObjectManagerFactory(BP, $_SERVER);
$magentoObjectManager = $magentoObjectManagerFactory->create($_SERVER);
// Remove previously generated static classes
$fs = $magentoObjectManager->create(Filesystem::class);
$fs->getDirectoryWrite(DirectoryList::ROOT)->delete('dev/tests/functional/generated/');
// Generate factories for old end-to-end tests
$magentoObjectManager->create(\Magento\Mtf\Util\Generate\Factory::class)->launch();

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
