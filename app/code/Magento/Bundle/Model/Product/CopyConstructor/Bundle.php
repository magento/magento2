<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Model\Product\CopyConstructor;

class Bundle implements \Magento\Catalog\Model\Product\CopyConstructorInterface
{
    /**
     * Duplicating bundle options and selections
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Catalog\Model\Product $duplicate
     * @return void
     */
    public function build(\Magento\Catalog\Model\Product $product, \Magento\Catalog\Model\Product $duplicate)
    {
        if ($product->getTypeId() != \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE) {
            //do nothing if not bundle
            return;
        }

        $product->getTypeInstance()->setStoreFilter($product->getStoreId(), $product);
        $optionCollection = $product->getTypeInstance()->getOptionsCollection($product);
        $selectionCollection = $product->getTypeInstance()->getSelectionsCollection(
            $product->getTypeInstance()->getOptionsIds($product),
            $product
        );
        $optionCollection->appendSelections($selectionCollection);

        $optionRawData = [];
        $selectionRawData = [];

        $i = 0;
        foreach ($optionCollection as $option) {
            $optionRawData[$i] = [
                'required' => $option->getData('required'),
                'position' => $option->getData('position'),
                'type' => $option->getData('type'),
                'title' => $option->getData('title') ? $option->getData('title') : $option->getData('default_title'),
                'delete' => '',
            ];
            foreach ($option->getSelections() as $selection) {
                $selectionRawData[$i][] = [
                    'product_id' => $selection->getProductId(),
                    'position' => $selection->getPosition(),
                    'is_default' => $selection->getIsDefault(),
                    'selection_price_type' => $selection->getSelectionPriceType(),
                    'selection_price_value' => $selection->getSelectionPriceValue(),
                    'selection_qty' => $selection->getSelectionQty(),
                    'selection_can_change_qty' => $selection->getSelectionCanChangeQty(),
                    'delete' => '',
                ];
            }
            $i++;
        }

        $duplicate->setBundleOptionsData($optionRawData);
        $duplicate->setBundleSelectionsData($selectionRawData);
    }
}
