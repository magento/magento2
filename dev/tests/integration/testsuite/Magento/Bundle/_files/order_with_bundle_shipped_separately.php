<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Bundle\Api\Data\LinkInterface;
use Magento\Bundle\Api\Data\LinkInterfaceFactory;
use Magento\Bundle\Api\Data\OptionInterfaceFactory;
use Magento\Bundle\Model\Option;
use Magento\Bundle\Model\Product\Price;
use Magento\Bundle\Model\Product\Type;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type\AbstractType;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Sales\Api\OrderPaymentRepositoryInterface as PaymentFactory;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Address;
use Magento\Sales\Model\Order\Item;
use Magento\Sales\Model\Order\ItemFactory;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\OrderRepository;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/products.php');

/** @var ObjectManagerInterface $objectManager */
$objectManager = Bootstrap::getObjectManager();
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
$sampleProduct = $productRepository->get('simple');
$secondSampleProduct = $productRepository->get('custom-design-simple-product');
$productFactory = $objectManager->get(ProductFactory::class);

/** @var $product Product */
$product = $productFactory->create();
$product->setTypeId(Type::TYPE_CODE)
    ->setId(3)
    ->setAttributeSetId($product->getDefaultAttributeSetId())
    ->setWebsiteIds([1])
    ->setName('Bundle Product')
    ->setSku('bundle-product')
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setStatus(Status::STATUS_ENABLED)
    ->setStockData(['use_config_manage_stock' => 1, 'qty' => 100, 'is_qty_decimal' => 0, 'is_in_stock' => 1])
    ->setPriceView(1)
    ->setPriceType(Price::PRICE_TYPE_FIXED)
    ->setShipmentType(AbstractType::SHIPMENT_SEPARATELY)
    ->setPrice(10.0)
    ->setBundleOptionsData(
        [
            [
                'title' => 'Bundle Product Items',
                'default_title' => 'Bundle Product Items',
                'type' => 'select',
                'required' => 1,
                'delete' => '',
            ],
            [
                'title' => 'Bundle Product Items Option 2',
                'default_title' => 'Bundle Product Items Option 2',
                'type' => 'select',
                'required' => 1,
                'delete' => '',
            ],
        ],
    )
    ->setBundleSelectionsData(
        [
            [
                [
                    'product_id' => $sampleProduct->getId(),
                    'selection_qty' => 1,
                    'selection_can_change_qty' => 1,
                    'delete' => '',
                ],
            ],
            [
                [
                    'product_id' => $secondSampleProduct->getId(),
                    'selection_qty' => 1,
                    'selection_can_change_qty' => 1,
                    'delete' => '',
                ],
            ],
        ],
    );

if ($product->getBundleOptionsData()) {
    $options = [];
    foreach ($product->getBundleOptionsData() as $key => $optionData) {
        if (!(bool)$optionData['delete']) {
            $option = $objectManager->get(OptionInterfaceFactory::class)->create(['data' => $optionData]);
            $option->setSku($product->getSku());
            $option->setOptionId(null);

            $links = [];
            $bundleLinks = $product->getBundleSelectionsData();
            if (!empty($bundleLinks[$key])) {
                foreach ($bundleLinks[$key] as $linkData) {
                    if (!(bool)$linkData['delete']) {
                        /** @var LinkInterface $link */
                        $link = $objectManager->get(LinkInterfaceFactory::class)->create(['data' => $linkData]);
                        $linkProduct = $productRepository->getById($linkData['product_id']);
                        $link->setSku($linkProduct->getSku());
                        $link->setQty($linkData['selection_qty']);
                        if (isset($linkData['selection_can_change_qty'])) {
                            $link->setCanChangeQuantity($linkData['selection_can_change_qty']);
                        }
                        $links[] = $link;
                    }
                }
                $option->setProductLinks($links);
                $options[] = $option;
            }
        }
    }
    $extension = $product->getExtensionAttributes();
    $extension->setBundleProductOptions($options);
    $product->setExtensionAttributes($extension);
}
$productRepository->save($product);

$addressData = include __DIR__ . '/../../../Magento/Sales/_files/address_data.php';

$billingAddress = $objectManager->create(Address::class, ['data' => $addressData]);
$billingAddress->setAddressType('billing');

$shippingAddress = clone $billingAddress;
$shippingAddress->setId(null)->setAddressType('shipping');

$paymentFactory = $objectManager->get(PaymentFactory::class);
$payment = $paymentFactory->create()->setMethod('checkmo');

$product = $productRepository->getById(3);

/** @var $typeInstance Type */
$typeInstance = $product->getTypeInstance();
$typeInstance->setStoreFilter($product->getStoreId(), $product);
$optionCollection = $typeInstance->getOptionsCollection($product);

$bundleOptions = [];
$bundleOptionsQty = [];
$optionsData = [];
foreach ($optionCollection as $option) {
    /** @var $option Option */
    $selectionsCollection = $typeInstance->getSelectionsCollection([$option->getId()], $product);
    if ($option->isMultiSelection()) {
        $optionsData[$option->getId()] = array_column($selectionsCollection->toArray(), 'product_id');
        $bundleOptions[$option->getId()] = array_column($selectionsCollection->toArray(), 'selection_id');
    } else {
        $bundleOptions[$option->getId()] = $selectionsCollection->getFirstItem()->getSelectionId();
        $optionsData[$option->getId()] = $selectionsCollection->getFirstItem()->getProductId();
    }
    $bundleOptionsQty[$option->getId()] = 1;
}

$requestInfo = [
    'product' => $product->getId(),
    'bundle_option' => $bundleOptions,
    'bundle_option_qty' => $bundleOptionsQty,
    'qty' => 1,
];

$orderItemFactory = $objectManager->get(ItemFactory::class);
$orderItems = [];
/** @var Item $orderItem */
$orderItem = $orderItemFactory->create();
$orderItem->setProductId($product->getId());
$orderItem->setQtyOrdered(1);
$orderItem->setBasePrice($product->getPrice());
$orderItem->setPrice($product->getPrice());
$orderItem->setRowTotal($product->getPrice());
$orderItem->setProductType($product->getTypeId());
$orderItem->setSku($product->getSku());
$orderItem->setProductOptions([
    'info_buyRequest' => $requestInfo,
    'shipment_type' => AbstractType::SHIPMENT_SEPARATELY
]);

$orderItems[] = $orderItem;

foreach ($optionsData as $optionId => $productId) {
    /** @var $selectedProduct Product */
    $selectedProduct = $productRepository->getById($productId);

    /** @var Item $orderItem */
    $orderItem = $orderItemFactory->create();
    $orderItem->setProductId($productId);
    $orderItem->setQtyOrdered(1);
    $orderItem->setBasePrice($selectedProduct->getPrice());
    $orderItem->setPrice($selectedProduct->getPrice());
    $orderItem->setRowTotal($selectedProduct->getPrice());
    $orderItem->setProductType($selectedProduct->getTypeId());
    $orderItem->setSku($selectedProduct->getSku());
    $orderItem->setProductOptions([
        'info_buyRequest' => $requestInfo,
        'shipment_type' => AbstractType::SHIPMENT_SEPARATELY
    ]);
    $orderItem->setParentItem($orderItems[0]);
    $orderItems[] = $orderItem;
}

$orderFactory = $objectManager->get(OrderFactory::class);
$orderRepository = $objectManager->get(OrderRepository::class);
/** @var Order $order */
$order = $orderFactory->create();
$order->setIncrementId('100000001');
$order->setState(Order::STATE_NEW);
$order->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_NEW));
$order->setCustomerIsGuest(true);
$order->setCustomerEmail('customer@null.com');
$order->setCustomerFirstname('firstname');
$order->setCustomerLastname('lastname');
$order->setBillingAddress($billingAddress);
$order->setShippingAddress($shippingAddress);
$order->setAddresses([$billingAddress, $shippingAddress]);
$order->setPayment($payment);

foreach ($orderItems as $item) {
    $order->addItem($item);
}

$order->setStoreId($objectManager->get(StoreManagerInterface::class)->getStore()->getId());
$order->setSubtotal(100);
$order->setBaseSubtotal(100);
$order->setBaseGrandTotal(100);
$orderRepository->save($order);
