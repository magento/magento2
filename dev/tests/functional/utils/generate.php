<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
require_once dirname(__FILE__) . '/' . 'bootstrap.php';

// Generate fixtures
$magentoObjectManagerFactory = \Magento\Framework\App\Bootstrap::createObjectManagerFactory(BP, $_SERVER);
$magentoObjectManager = $magentoObjectManagerFactory->create($_SERVER);

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
