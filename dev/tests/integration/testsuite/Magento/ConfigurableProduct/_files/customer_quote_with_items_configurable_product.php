<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute as ProductAttribute;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Eav\Model\Config;
use Magento\Framework\DataObject;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;

require __DIR__ . '/configurable_products.php';
require __DIR__ . '/../../Customer/_files/customer_with_uk_address.php';

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
/** @var Config $eavConfig */
$eavConfig = $objectManager->get(Config::class);
/** @var ProductAttribute $attribute */
$attribute = $eavConfig->getAttribute(ProductAttributeInterface::ENTITY_TYPE_CODE, 'test_configurable');

$customer = $customerRepository->get('customer_uk_address@test.com');
$quote->setStoreId($storeManager->getStore()->getId())
    ->setIsActive(true)
    ->setIsMultiShipping(false)
    ->setReservedOrderId('customer_quote_configurable_products')
    ->assignCustomer($customer);

$attributeOptions = $attribute->getOptions();
unset($attributeOptions[0]);
$productConfigurable = $productRepository->get('configurable');
/** @var DataObject $request */
$request = $objectManager->create(DataObject::class);

foreach ($attributeOptions as $attributeOption) {
    $productConfigurable = clone $productConfigurable;
    $request->setData(
        [
            'product_id' => $productConfigurable->getId(),
            'super_attribute' => [
                $attribute->getAttributeId() => $attributeOption->getValue()
            ],
            'qty' => 1
        ]
    );
    $quote->addProduct($productConfigurable, $request);
}
$quoteRepository->save($quote);
