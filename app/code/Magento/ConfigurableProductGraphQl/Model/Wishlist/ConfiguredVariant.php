<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProductGraphQl\Model\Wishlist;

use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use Magento\CatalogGraphQl\Model\ProductDataProvider;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Fetches the data of selected variant of configurable product
 */
class ConfiguredVariant implements ResolverInterface
{
    /**
     * @var ProductDataProvider
     */
    private $productDataProvider;

    /**
     * @param ProductDataProvider $productDataProvider
     */
    public function __construct(ProductDataProvider $productDataProvider)
    {
        $this->productDataProvider = $productDataProvider;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!$value['itemModel'] instanceof ItemInterface) {
            throw new LocalizedException(__('"itemModel" should be a "%instance" instance', [
                'instance' => ItemInterface::class
            ]));
        }

        $item = $value['itemModel'];
        $product = $item->getProduct();
        $option = $product->getCustomOption('simple_product');

        return $option && $option->getProduct()
            ? $this->productDataProvider->getProductDataById((int) $option->getProduct()->getId())
            : null;
    }
}
