<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Catalog\Model\Product\Option\Type\File\ValidatorFile;
use Magento\Framework\DataObject;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Model\QuoteRepository;
use Magento\TestFramework\Catalog\Model\Product\Option\Type\File\ValidatorFileMock;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Checkout/_files/quote_with_address.php');
Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/product_with_options.php');

$objectManager = Bootstrap::getObjectManager();
/** @var QuoteFactory $quoteFactory */
$quoteFactory = $objectManager->get(QuoteFactory::class);
/** @var QuoteResource $quoteResource */
$quoteResource = $objectManager->get(QuoteResource::class);
$quote = $quoteFactory->create();
$quoteResource->load($quote, 'test_order_1', 'reserved_order_id');
/** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
$product = $productRepository->get('simple');

$options = [];
/** @var $option \Magento\Catalog\Model\Product\Option */
foreach ($product->getOptions() as $option) {
    switch ($option->getGroupByType()) {
        case \Magento\Catalog\Api\Data\ProductCustomOptionInterface::OPTION_GROUP_DATE:
            $value = ['year' => 2013, 'month' => 8, 'day' => 9, 'hour' => 13, 'minute' => 35];
            break;
        case \Magento\Catalog\Api\Data\ProductCustomOptionInterface::OPTION_GROUP_SELECT:
            $value = key($option->getValues());
            break;
        case \Magento\Catalog\Api\Data\ProductCustomOptionInterface::OPTION_GROUP_FILE:
            $value = 'test.jpg';
            break;
        default:
            $value = 'test';
            break;
    }
    $options[$option->getId()] = $value;
}

$requestInfo = new DataObject(['qty' => 1, 'options' => $options]);
$validatorFile = $objectManager->create(ValidatorFileMock::class, ['name' => 'testName'])->getInstance();
$objectManager->addSharedInstance($validatorFile, ValidatorFile::class);

$quote->setReservedOrderId('test_order_item_with_items_and_custom_options');
$quote->addProduct($product, $requestInfo);
$quote->collectTotals();
$objectManager->get(QuoteRepository::class)->save($quote);

/** @var QuoteIdMask $quoteIdMask */
$quoteIdMask = Bootstrap::getObjectManager()
    ->create(QuoteIdMaskFactory::class)
    ->create();
$quoteIdMask->setQuoteId($quote->getId());
$quoteIdMask->setDataChanges(true);
$quoteIdMask->save();
