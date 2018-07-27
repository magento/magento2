<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
require_once dirname(__FILE__) . '/' . 'bootstrap.php';

// Generate page
$objectManager->create('Magento\Mtf\Util\Generate\Page')->launch();

// Generate fixtures
$magentoObjectManagerFactory = \Magento\Framework\App\Bootstrap::createObjectManagerFactory(BP, $_SERVER);
$magentoObjectManager = $magentoObjectManagerFactory->create($_SERVER);
$objectManager->create('Magento\Mtf\Util\Generate\Fixture')->launch();

// Generate repositories
$magentoObjectManager->get('Magento\Framework\App\State')->setAreaCode('frontend');
$objectManager->create('Magento\Mtf\Util\Generate\Repository')->launch();

// Generate factories for old end-to-end tests
$magentoObjectManager->create('Magento\Mtf\Util\Generate\Factory')->launch();

\Magento\Mtf\Util\Generate\GenerateResult::displayResults();
