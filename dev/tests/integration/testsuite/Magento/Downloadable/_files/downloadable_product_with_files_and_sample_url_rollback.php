<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Downloadable\Api\DomainManagerInterface;

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/** @var DomainManagerInterface $domainManager */
$domainManager = $objectManager->get(DomainManagerInterface::class);
$domainManager->removeDomains(['sampleurl.com']);

// phpcs:ignore Magento2.Security.IncludeFile
require __DIR__ . '/product_downloadable_rollback.php';
