<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Downloadable\Api\DomainManagerInterface;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var DomainManagerInterface $domainManager */
$domainManager = $objectManager->get(DomainManagerInterface::class);
$domainManager->removeDomains(['sampleurl.com']);

Resolver::getInstance()->requireDataFixture('Magento/Downloadable/_files/product_downloadable_rollback.php');
