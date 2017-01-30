<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

require __DIR__ . '/../../../Magento/Catalog/_files/multiple_products.php';

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

$productIds = range(10, 12, 1);
foreach ($productIds as $productId) {
    /** @var \Magento\CatalogInventory\Model\Stock\Item $stockItem */
    $stockItem = $objectManager->create('Magento\CatalogInventory\Model\Stock\Item');
    $stockItem->load($productId, 'product_id');

    if (!$stockItem->getProductId()) {
        $stockItem->setProductId($productId);
    }
    $stockItem->setUseConfigManageStock(1);
    $stockItem->setQty(1000);
    $stockItem->setIsQtyDecimal(0);
    $stockItem->setIsInStock(1);
    $stockItem->save();
}

/** @var $product \Magento\Catalog\Model\Product */
$product = $objectManager->create('Magento\Catalog\Model\Product');
$product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_BUNDLE)
    ->setId(3)
    ->setAttributeSetId(4)
    ->setWebsiteIds([1])
    ->setName('Bundle Product')
    ->setSku('bundle-product')
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setStockData(['use_config_manage_stock' => 1, 'qty' => 100, 'is_qty_decimal' => 0, 'is_in_stock' => 1])
    ->setPriceView(1)
    ->setPriceType(1)
    ->setPrice(10.0)
    ->setBundleOptionsData(
        [
            // Required "Drop-down" option
            [
                'title' => 'Option 1',
                'default_title' => 'Option 1',
                'type' => 'select',
                'required' => 1,
                'delete' => '',
            ],
            // Required "Radio Buttons" option
            [
                'title' => 'Option 2',
                'default_title' => 'Option 2',
                'type' => 'radio',
                'required' => 1,
                'delete' => '',
            ],
            // Required "Checkbox" option
            [
                'title' => 'Option 3',
                'default_title' => 'Option 3',
                'type' => 'checkbox',
                'required' => 1,
                'delete' => '',
            ],
            // Required "Multiple Select" option
            [
                'title' => 'Option 4',
                'default_title' => 'Option 4',
                'type' => 'multi',
                'required' => 1,
                'delete' => '',
            ],
            // Non-required "Multiple Select" option
            [
                'title' => 'Option 5',
                'default_title' => 'Option 5',
                'type' => 'multi',
                'required' => 0,
                'delete' => '',
            ]
        ]
    )->setBundleSelectionsData(
        [
            [
                [
                    'product_id' => 10,
                    'selection_qty' => 1,
                    'selection_can_change_qty' => 1,
                    'delete' => '',
                    'option_id' => 1
                ],
                [
                    'product_id' => 11,
                    'selection_qty' => 1,
                    'selection_can_change_qty' => 1,
                    'delete' => '',
                    'option_id' => 1
                ]
            ],
            [
                [
                    'product_id' => 10,
                    'selection_qty' => 1,
                    'selection_can_change_qty' => 1,
                    'delete' => '',
                    'option_id' => 2
                ],
                [
                    'product_id' => 11,
                    'selection_qty' => 1,
                    'selection_can_change_qty' => 1,
                    'delete' => '',
                    'option_id' => 2
                ]
            ],
            [
                [
                    'product_id' => 10,
                    'selection_qty' => 1,
                    'delete' => '',
                    'option_id' => 3
                ],
                [
                    'product_id' => 11,
                    'selection_qty' => 1,
                    'delete' => '',
                    'option_id' => 3
                ]
            ],
            [
                [
                    'product_id' => 10,
                    'selection_qty' => 1,
                    'delete' => '',
                    'option_id' => 4
                ],
                [
                    'product_id' => 11,
                    'selection_qty' => 1,
                    'delete' => '',
                    'option_id' => 4
                ]
            ],
            [
                [
                    'product_id' => 10,
                    'selection_qty' => 1,
                    'delete' => '',
                    'option_id' => 5
                ],
                [
                    'product_id' => 11,
                    'selection_qty' => 1,
                    'delete' => '',
                    'option_id' => 5
                ]
            ]
        ]
    )->save();
