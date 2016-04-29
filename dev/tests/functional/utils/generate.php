<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
require_once dirname(__FILE__) . '/' . 'bootstrap.php';

// Generate page
$objectManager->create(\Magento\Mtf\Util\Generate\Page::class)->launch();

// Generate fixtures
$magentoObjectManagerFactory = \Magento\Framework\App\Bootstrap::createObjectManagerFactory(BP, $_SERVER);
$magentoObjectManager = $magentoObjectManagerFactory->create($_SERVER);
$objectManager->create(\Magento\Mtf\Util\Generate\Fixture::class)->launch();

// Generate repositories
$magentoObjectManager->get(\Magento\Framework\App\State::class)->setAreaCode('frontend');
$objectManager->create(\Magento\Mtf\Util\Generate\Repository::class)->launch();

// Generate factories for old end-to-end tests
$magentoObjectManager->create(\Magento\Mtf\Util\Generate\Factory::class)->launch();

\Magento\Mtf\Util\Generate\GenerateResult::displayResults();
