<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Configuration\Item;

use Magento\Catalog\Api\Data\ProductInterface;

/**
 * {@inheritdoc}
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
    public function getFinalProduct(ItemInterface $item) : ProductInterface
    {
        $product = $item->getProduct();
        foreach ($this->itemResolvers as $resolver) {
            $resolvedProduct = $resolver->getFinalProduct($item);
            if ($resolvedProduct !== $product) {
                $product = $resolvedProduct;
                break;
            }
        }
        return $product;
    }
}
