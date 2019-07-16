<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Downloadable\Console\Command\DomainsRemoveCommand;
use Magento\Framework\Exception\NoSuchEntityException;

\Magento\TestFramework\Helper\Bootstrap::getInstance()->getInstance()->reinitialize();

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var DomainsRemoveCommand $domainsRemoveCommand */
$domainsRemoveCommand = $objectManager->get(DomainsRemoveCommand::class);
$command = new \Symfony\Component\Console\Tester\CommandTester($domainsRemoveCommand);
$command->execute(
    [
        DomainsRemoveCommand::INPUT_KEY_DOMAINS => [
            'example.com',
            'www.example.com',
            'www.sample.example.com',
            'google.com'
        ]
    ]
);

/** @var \Magento\Framework\Registry $registry */
$registry = $objectManager->get(\Magento\Framework\Registry::class);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
$productRepository = $objectManager
    ->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
try {
    $product = $productRepository->get('downloadable-product', false, null, true);
    $productRepository->delete($product);
} catch (NoSuchEntityException $e) { // @codingStandardsIgnoreLine
}
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
