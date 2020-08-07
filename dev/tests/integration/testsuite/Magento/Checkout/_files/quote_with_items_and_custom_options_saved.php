<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\Data\ProductCustomOptionInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Option;
use Magento\Catalog\Model\Product\Option\Type\File\ValidatorFile;
use Magento\Framework\DataObject;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Model\QuoteRepository;
use Magento\TestFramework\Catalog\Model\Product\Option\Type\File\ValidatorFileMock;
use Magento\TestFramework\Helper\Bootstrap;

require __DIR__ . '/../../Checkout/_files/quote_with_address.php';
require __DIR__ . '/../../Catalog/_files/product_with_options.php';

$objectManager = Bootstrap::getObjectManager();
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->create(ProductRepositoryInterface::class);
$product = $productRepository->get('simple');

$options = [];
/** @var $option Option */
foreach ($product->getOptions() as $option) {
    switch ($option->getGroupByType()) {
        case ProductCustomOptionInterface::OPTION_GROUP_DATE:
            $value = ['year' => 2013, 'month' => 8, 'day' => 9, 'hour' => 13, 'minute' => 35];
            break;
        case ProductCustomOptionInterface::OPTION_GROUP_SELECT:
            $value = key($option->getValues());
            break;
        case ProductCustomOptionInterface::OPTION_GROUP_FILE:
            $value = 'test.jpg';
            break;
        default:
            $value = 'test';
            break;
    }
    $options[$option->getId()] = $value;
}

$requestInfo = new DataObject(['qty' => 1, 'options' => $options]);
$validatorFile = $objectManager->get(ValidatorFileMock::class)->getInstance();
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
