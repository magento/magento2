<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Plugin\Catalog\Model\Product\Type;

use Magento\Catalog\Model\Product\Type\AbstractType as Subject;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type as BundleType;

/**
 * Plugin to add possibility to add bundle product with single option from list
 */
class AbstractType
{
    /**
     * Add possibility to add to cart from the list in case of one required option
     *
     * @param Subject $subject
     * @param bool $result
     * @param Product $product
     * @return bool
     */
    public function afterIsPossibleBuyFromList(Subject $subject, $result, $product)
    {
        if ($product->getTypeId() === BundleType::TYPE_BUNDLE) {
            $typeInstance = $product->getTypeInstance();
            $typeInstance->setStoreFilter($product->getStoreId(), $product);

            $optionsIds = $typeInstance->getOptionsIds($product);
            if (count($optionsIds) === 1 && $typeInstance->hasRequiredOptions($product)) {
                $selectionsCollection = $typeInstance->getSelectionsCollection(
                    $optionsIds,
                    $product
                );
                $selections = $selectionsCollection->exportToArray();
                if (count($selections) === 1) {
                    $selection = array_pop($selections);
                    $result = (int) $selection['is_default'] === 1
                        && (int) $selection['selection_can_change_qty'] === 0;
                }
            }
        }
        return $result;
    }
}
