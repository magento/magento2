<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Configuration\Item;

/**
 * Composite implementation for @see ItemResolverInterface
 */
class ItemResolverComposite implements ItemResolverInterface
{
    /** @var ItemResolverInterface[] */
    private $itemResolvers = [];

    /**
     * @param ItemResolverInterface[] $itemResolvers
     */
    public function __construct(array $itemResolvers)
    {
        $this->itemResolvers = $itemResolvers;
    }

    /**
     * {@inheritdoc}
     */
    public function getFinalProduct(
        \Magento\Catalog\Model\Product\Configuration\Item\ItemInterface $item
    ) : \Magento\Catalog\Api\Data\ProductInterface {
        $product = $item->getProduct();
        foreach ($this->itemResolvers as $resolver) {
            $resolvedProduct = $resolver->getFinalProduct($item);
            if ($resolvedProduct !== $product) {
                return $resolvedProduct;
            }
        }
        return $product;
    }
}
