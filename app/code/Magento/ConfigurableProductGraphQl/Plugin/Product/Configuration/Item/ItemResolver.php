<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProductGraphQl\Plugin\Product\Configuration\Item;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use Magento\ConfigurableProduct\Model\Product\Configuration\Item\ItemProductResolver;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

/**
 * Plugin for item resolver
 */
class ItemResolver
{
    /**
     * After plugin for final product
     *
     * @param ItemProductResolver $subject
     * @param $result
     * @param ItemInterface $item
     * @return ProductInterface
     */
    public function afterGetFinalProduct(ItemProductResolver $subject, $result, ItemInterface $item): ProductInterface
    {
        if ($result->getTypeId() === Configurable::TYPE_CODE) {
            $option = $item->getOptionByCode('simple_product');
            $result = $option ? $option->getProduct() : $item->getProduct();
        }

        return $result;
    }
}
