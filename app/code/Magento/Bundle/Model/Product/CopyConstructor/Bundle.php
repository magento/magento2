<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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

        $optionRawData = array();
        $selectionRawData = array();

        $i = 0;
        foreach ($optionCollection as $option) {
            $optionRawData[$i] = array(
                'required' => $option->getData('required'),
                'position' => $option->getData('position'),
                'type' => $option->getData('type'),
                'title' => $option->getData('title') ? $option->getData('title') : $option->getData('default_title'),
                'delete' => ''
            );
            foreach ($option->getSelections() as $selection) {
                $selectionRawData[$i][] = array(
                    'product_id' => $selection->getProductId(),
                    'position' => $selection->getPosition(),
                    'is_default' => $selection->getIsDefault(),
                    'selection_price_type' => $selection->getSelectionPriceType(),
                    'selection_price_value' => $selection->getSelectionPriceValue(),
                    'selection_qty' => $selection->getSelectionQty(),
                    'selection_can_change_qty' => $selection->getSelectionCanChangeQty(),
                    'delete' => ''
                );
            }
            $i++;
        }

        $duplicate->setBundleOptionsData($optionRawData);
        $duplicate->setBundleSelectionsData($selectionRawData);
    }
}
