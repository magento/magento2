<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GroupedProduct\Model\Wishlist\Product;

use Magento\Wishlist\Model\Item as WishlistItem;
use Magento\GroupedProduct\Model\Product\Type\Grouped as TypeGrouped;
use Magento\Catalog\Model\Product;

/**
 * Wishlist logic for grouped product
 */
class Item
{
    /**
     * Modify Wishlist item based on associated product qty
     *
     * @param WishlistItem $subject
     * @param Product $product
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeRepresentProduct(
        WishlistItem $subject,
        Product $product
    ): array {
        if ($product->getTypeId() === TypeGrouped::TYPE_CODE
            && $product->getId() === $subject->getProduct()->getId()
        ) {
            $itemOptions = $subject->getOptionsByCode();
            $productOptions = $product->getCustomOptions();

            $diff = array_diff_key($itemOptions, $productOptions);

            if (!$diff) {
                $buyRequest = $subject->getBuyRequest();
                $superGroupInfo = $buyRequest->getData('super_group');

                foreach ($itemOptions as $key => $itemOption) {
                    if (preg_match('/associated_product_\d+/', $key)) {
                        $simpleId = str_replace('associated_product_', '', $key);
                        $prodQty = $productOptions[$key]->getValue();

                        $itemOption->setValue($itemOption->getValue() + $prodQty);

                        if (isset($superGroupInfo[$simpleId])) {
                            $superGroupInfo[$simpleId] = $itemOptions[$key]->getValue();
                        }
                    }
                }

                $buyRequest->setData('super_group', $superGroupInfo);

                $subject->setOptions($itemOptions);
                $subject->mergeBuyRequest($buyRequest);
            }
        }

        return [$product];
    }

    /**
     * Remove associated_product_id key. associated_product_id contains qty
     *
     * @param WishlistItem $subject
     * @param array $options1
     * @param array $options2
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeCompareOptions(
        WishlistItem $subject,
        array $options1,
        array $options2
    ): array {
        $diff = array_diff_key($options1, $options2);

        if (!$diff) {
            foreach (array_keys($options1) as $key) {
                if (preg_match('/associated_product_\d+/', $key)) {
                    unset($options1[$key]);
                    unset($options2[$key]);
                }
            }
        }

        return [$options1, $options2];
    }
}
