<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
require_once dirname(__DIR__) . '/' . 'bootstrap.php';

$magentoObjectManagerFactory = \Magento\Framework\App\Bootstrap::createObjectManagerFactory(BP, $_SERVER);
$magentoObjectManager = $magentoObjectManagerFactory->create($_SERVER);
$objectManager->create(\Magento\Mtf\Util\Generate\Fixture::class)->launch();
