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
use Magento\Catalog\Model\Product\Option\Value;
use Magento\TestFramework\Catalog\Model\Product\Option\Type\File\ValidatorFileMock;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\DataObject;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/product_with_options.php');
Resolver::getInstance()->requireDataFixture('Magento/Customer/_files/customer_with_uk_address.php');

/** @var ObjectManager $objectManager */
$objectManager = Bootstrap::getObjectManager();
/** @var StoreManagerInterface $storeManager */
$storeManager = $objectManager->get(StoreManagerInterface::class);
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
$productRepository->cleanCache();
/** @var CustomerRepositoryInterface $customerRepository */
$customerRepository = $objectManager->get(CustomerRepositoryInterface::class);
/** @var Quote $quote */
$quote = $objectManager->get(QuoteFactory::class)->create();
/** @var CartRepositoryInterface $quoteRepository */
$quoteRepository = $objectManager->get(CartRepositoryInterface::class);

$customer = $customerRepository->get('customer_uk_address@test.com');
$product = $productRepository->get('simple');
$options = [];
$dropDownValues = [];
$iDate = 1;
/** @var Option $option */
foreach ($product->getOptions() as $option) {
    switch ($option->getGroupByType()) {
        case ProductCustomOptionInterface::OPTION_GROUP_SELECT:
            if ($option->getType() == ProductCustomOptionInterface::OPTION_TYPE_DROP_DOWN) {
                $dropDownValues = $option->getValues();
                $value = null;
            } elseif ($option->getType() == ProductCustomOptionInterface::OPTION_TYPE_CHECKBOX) {
                $value = array_keys($option->getValues());
            } else {
                $value = (string)key($option->getValues());
            }
            break;
        case ProductCustomOptionInterface::OPTION_GROUP_DATE:
            $value = [
                'year' => 2013 + $iDate,
                'month' => 1 + $iDate,
                'day' => 1 + $iDate,
                'hour' => 10 + $iDate,
                'minute' => 30 + $iDate,
            ];
            $iDate++;
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

$itemsOptions = [];
/** @var Value $dropDownValue */
foreach ($dropDownValues as $dropDownId => $dropDownValue) {
    $options[$dropDownValue->getOption()->getId()] = $dropDownId;
    $itemsOptions[$dropDownValue->getTitle()] = $options;
}

$validatorFileMock = $objectManager->get(ValidatorFileMock::class)->getInstance();
$objectManager->addSharedInstance($validatorFileMock, ValidatorFile::class);

$quote->setStoreId($storeManager->getStore()->getId())
    ->assignCustomer($customer)
    ->setReservedOrderId('customer_quote_product_custom_options');

/** @var DataObject $request */
$requestInfo = $objectManager->create(DataObject::class);

foreach ($itemsOptions as $itemOptions) {
    $requestInfo->setData(['qty' => 1, 'options' => $itemOptions]);
    $product = clone $product;
    $quote->addProduct($product, $requestInfo);
}

$quoteRepository->save($quote);
$objectManager->removeSharedInstance(ValidatorFile::class);
