<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
\Magento\TestFramework\Helper\Bootstrap::getInstance()->loadArea('frontend');
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/** Create simple and bundle products for quote*/
$simpleProducts[] = $objectManager->create(\Magento\Catalog\Model\Product::class)
    ->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
    ->setAttributeSetId(4)
    ->setWebsiteIds([1])
    ->setName('Simple Product 1')
    ->setSku('simple-1')
    ->setPrice(10)
    ->setDescription('Description with <b>html tag</b>')
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setCategoryIds([2])
    ->setStockData(['use_config_manage_stock' => 1, 'qty' => 100, 'is_qty_decimal' => 0, 'is_in_stock' => 1])
    ->save();

$simpleProducts[] = $objectManager->create(\Magento\Catalog\Model\Product::class)
    ->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
    ->setAttributeSetId(4)
    ->setWebsiteIds([1])
    ->setName('Simple Product 2')
    ->setSku('simple-2')
    ->setPrice(10)
    ->setDescription('Description with <b>html tag</b>')
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setCategoryIds([2])
    ->setStockData(['use_config_manage_stock' => 1, 'qty' => 100, 'is_qty_decimal' => 0, 'is_in_stock' => 1])
    ->save();
$productRepository = $objectManager->get(Magento\Catalog\Api\ProductRepositoryInterface::class);
/**
 * @var \Magento\Catalog\Model\Product $product
 */
$product = $objectManager->create(\Magento\Catalog\Model\Product::class);
$product
    ->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_BUNDLE)
    ->setAttributeSetId(4)
    ->setWebsiteIds([1])
    ->setName('Bundle Product')
    ->setSku('bundle-product')
    ->setDescription('Description with <b>html tag</b>')
    ->setShortDescription('Bundle')
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setStockData(
        [
            'use_config_manage_stock' => 0,
            'manage_stock' => 0,
            'use_config_enable_qty_increments' => 1,
            'use_config_qty_increments' => 1,
            'is_in_stock' => 0,
        ]
    )
    ->setBundleOptionsData(
        [
            [
                'title' => 'Bundle Product Items',
                'default_title' => 'Bundle Product Items',
                'type' => 'checkbox',
                'required' => 1,
                'delete' => '',
                'position' => 0,
                'option_id' => '',
            ],
        ]
    )
    ->setBundleSelectionsData(
        [
            [
                [
                    'product_id' => $simpleProducts[0]->getId(),
                    'selection_qty' => 1,
                    'selection_can_change_qty' => 1,
                    'delete' => '',
                    'position' => 0,
                    'selection_price_type' => 0,
                    'selection_price_value' => 0.0,
                    'option_id' => '',
                    'selection_id' => '',
                    'is_default' => 1,
                ],
                [
                    'product_id' => $simpleProducts[1]->getId(),
                    'selection_qty' => 1,
                    'selection_can_change_qty' => 1,
                    'delete' => '',
                    'position' => 0,
                    'selection_price_type' => 0,
                    'selection_price_value' => 0.0,
                    'option_id' => '',
                    'selection_id' => '',
                    'is_default' => 1,
                ]
            ],
        ]
    )->setCustomAttributes([
        "price_type" => [
            'attribute_code' => 'price_type',
            'value' => \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC
        ],
        "price_view" => [
            "attribute_code" => "price_view",
            "value" => "1",
        ],
    ])
    ->setCanSaveBundleSelections(true)
    ->setHasOptions(false)
    ->setAffectBundleProductSelections(true);
if ($product->getBundleOptionsData()) {
    $options = [];
    foreach ($product->getBundleOptionsData() as $key => $optionData) {
        if (!(bool)$optionData['delete']) {
            $option = $objectManager->create(\Magento\Bundle\Api\Data\OptionInterfaceFactory::class)
                ->create(['data' => $optionData]);
            $option->setSku($product->getSku());
            $option->setOptionId(null);

            $links = [];
            $bundleLinks = $product->getBundleSelectionsData();
            if (!empty($bundleLinks[$key])) {
                foreach ($bundleLinks[$key] as $linkData) {
                    if (!(bool)$linkData['delete']) {
                        /** @var \Magento\Bundle\Api\Data\LinkInterface$link */
                        $link = $objectManager->create(\Magento\Bundle\Api\Data\LinkInterfaceFactory::class)
                            ->create(['data' => $linkData]);
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

$product = $productRepository->get($product->getSku());
$bundleOptions = [];
$bundleOptionsQty = [];
/** @var $option \Magento\Bundle\Model\Option */
foreach ($product->getExtensionAttributes()->getBundleProductOptions() as $option) {
    foreach ($option->getProductLinks() as $selection) {
        /**
         * @var \Magento\Bundle\Api\Data\LinkInterface $selection
         */
        $bundleOptions[$option->getId()][] = $selection->getId();
        $bundleOptionsQty[$option->getId()][] = 1;
    }
}

$buyRequest = new \Magento\Framework\DataObject(
    ['qty' => 1, 'bundle_option' => $bundleOptions, 'bundle_option_qty' => $bundleOptionsQty]
);
$product->setSkipCheckRequiredOption(true);

$addressData = include __DIR__ . '/address_data.php';
$billingAddress = $objectManager->create(\Magento\Quote\Model\Quote\Address::class, ['data' => $addressData]);
$billingAddress->setAddressType('billing');

/** @var Magento\Quote\Model\Quote\Address $shippingAddress */
$shippingAddress = clone $billingAddress;
$shippingAddress->setId(null)->setAddressType('shipping');

/** @var \Magento\Quote\Model\Quote $quote */
$quote = $objectManager->create(\Magento\Quote\Model\Quote::class);
$quote
    ->setCustomerIsGuest(true)
    ->setStoreId($objectManager->get(\Magento\Store\Model\StoreManagerInterface::class)->getStore()->getId())
    ->setReservedOrderId('test01')
    ->setBillingAddress($billingAddress)
    ->setShippingAddress($shippingAddress)
    ->setCustomerEmail('test@test.magento.com')
    ->addProduct($product, $buyRequest);

/** @var $rate \Magento\Quote\Model\Quote\Address\Rate */
$rate = $objectManager->create(\Magento\Quote\Model\Quote\Address\Rate::class);
$rate
    ->setCode('freeshipping_freeshipping')
    ->getPrice(1);

$quote->getShippingAddress()->setShippingMethod('freeshipping_freeshipping');
$quote->getShippingAddress()->addShippingRate($rate);
$quote->getPayment()->setMethod('checkmo');
$quote->collectTotals();
$quote->save();

/** @var \Magento\Quote\Model\QuoteIdMask $quoteIdMask */
$quoteIdMask = $objectManager->create(\Magento\Quote\Model\QuoteIdMaskFactory::class)->create();
$quoteIdMask->setQuoteId($quote->getId());
$quoteIdMask->setDataChanges(true);
$quoteIdMask->save();
