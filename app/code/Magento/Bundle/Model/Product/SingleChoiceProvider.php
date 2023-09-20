<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Model\Product;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type as BundleType;

/**
 * Service to check is bundle product has single choice (no customization possible)
 */
class SingleChoiceProvider
{
    /**
     * Single choice availability
     *
     * @param Product $product
     * @return bool
     */
    public function isSingleChoiceAvailable(Product $product) : bool
    {
        $result = false;
        if ($product->getTypeId() === BundleType::TYPE_BUNDLE) {
            $typeInstance = $product->getTypeInstance();
            $typeInstance->setStoreFilter($product->getStoreId(), $product);

            if ($typeInstance->hasRequiredOptions($product)) {
                $options = $typeInstance->getOptions($product);
                $isNoCustomizations = true;
                foreach ($options as $option) {
                    $optionId = $option->getId();
                    $required = $option->getRequired();
                    if ($isNoCustomizations && (int) $required === 1) {
                        $selectionsCollection = $typeInstance->getSelectionsCollection(
                            [$optionId],
                            $product
                        );
                        $selections = $selectionsCollection->exportToArray();
                        if (count($selections) > 1) {
                            foreach ($selections as $selection) {
                                if ($isNoCustomizations) {
                                    $isNoCustomizations = (int)$selection['is_default'] === 1
                                        && (int)$selection['selection_can_change_qty'] === 0;
                                } else {
                                    break;
                                }
                            }
                        }
                    } else {
                        $isNoCustomizations = false;
                        break;
                    }
                }

                $result = $isNoCustomizations;
            }
        }
        return $result;
    }
}
