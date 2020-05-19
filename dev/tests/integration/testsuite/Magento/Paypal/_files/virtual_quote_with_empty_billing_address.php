<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Item;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Customer/_files/customer.php');

Bootstrap::getInstance()->loadArea('adminhtml');
$objectManager = Bootstrap::getObjectManager();

/** @var CustomerRepositoryInterface $customerRepository */
$customerRepository = $objectManager->create(CustomerRepositoryInterface::class);
$customer = $customerRepository->get('customer@example.com');

/** @var $product Product */
$product = $objectManager->create(Product::class);
$product->isObjectNew(true);
$product->setTypeId(Type::TYPE_VIRTUAL)
    ->setAttributeSetId(4)
    ->setWebsiteIds([1])
    ->setName('Virtual Product')
    ->setSku('virtual-product-express')
    ->setPrice(10)
    ->setWeight(1)
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setStatus(Status::STATUS_ENABLED);

/** @var StockItemInterface $stockItem */
$stockItem = $objectManager->create(StockItemInterface::class);
$stockItem->setQty(100)
    ->setIsInStock(true);
$extensionAttributes = $product->getExtensionAttributes();
$extensionAttributes->setStockItem($stockItem);

/** @var $productRepository ProductRepositoryInterface */
$productRepository = $objectManager->create(ProductRepositoryInterface::class);
$product = $productRepository->save($product);

$billingAddress = $objectManager->create(Address::class);
$billingAddress->setAddressType('billing');

/** @var $quote Quote */
$quote = $objectManager->create(Quote::class);
$quote->setCustomerIsGuest(false)
    ->setCustomerId($customer->getId())
    ->setCustomer($customer)
    ->setStoreId($objectManager->get(StoreManagerInterface::class)->getStore()->getId())
    ->setReservedOrderId('test02')
    ->setBillingAddress($billingAddress);
$item = $objectManager->create(Item::class);
$item->setProduct($product)
    ->setPrice($product->getPrice())
    ->setQty(1);
$quote->addItem($item);
$quote->getPayment()->setMethod(\Magento\Paypal\Model\Config::METHOD_EXPRESS);

/** @var CartRepositoryInterface $quoteRepository */
$quoteRepository = $objectManager->create(CartRepositoryInterface::class);
$quoteRepository->save($quote);
$quote = $quoteRepository->get($quote->getId());
