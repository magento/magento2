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
    public function isSingleChoiceAvailable(Product $product): bool
    {
        $result = false;
        if ($product->getTypeId() !== BundleType::TYPE_BUNDLE) {
            return $result;
        }

        if (!$this->hasRequiredOptions($product)) {
            return $result;
        }

        return $this->hasCustomizations($product);
    }

    /**
     * Options checker
     *
     * @param Product $product
     * @return bool
     */
    private function hasRequiredOptions(Product $product): bool
    {
        $result = true;
        $typeInstance = $product->getTypeInstance();
        $typeInstance->setStoreFilter($product->getStoreId(), $product);

        if (!$typeInstance->hasRequiredOptions($product)) {
            $result = false;
        }

        return $result;
    }

    /**
     * Customizations checker
     *
     * @param Product $product
     * @return bool
     */
    private function hasCustomizations(Product $product): bool
    {
        $typeInstance = $product->getTypeInstance();
        $isNoCustomizations = true;
        foreach ($typeInstance->getOptions($product) as $option) {
            if ($isNoCustomizations && (int)$option->getRequired() === 1) {
                $selectionsCollection = $typeInstance->getSelectionsCollection(
                    [$option->getId()],
                    $product
                );

                if ($selectionsCollection->count() > 1) {
                    foreach ($selectionsCollection as $selection) {
                        if ($isNoCustomizations) {
                            $isNoCustomizations = (int)$selection->getData('is_default') === 1
                                && (int)$selection->getData('selection_can_change_qty') === 0;
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

        return $isNoCustomizations;
    }
}
