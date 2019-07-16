<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Downloadable\Console\Command\DomainsRemoveCommand;

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var DomainsRemoveCommand $domainsRemoveCommand */
$domainsRemoveCommand = $objectManager->get(DomainsRemoveCommand::class);
$command = new \Symfony\Component\Console\Tester\CommandTester($domainsRemoveCommand);
$command->execute([DomainsRemoveCommand::INPUT_KEY_DOMAINS => ['sampleurl.com']]);

// @codingStandardsIgnoreLine
require __DIR__ . '/product_downloadable_rollback.php';
