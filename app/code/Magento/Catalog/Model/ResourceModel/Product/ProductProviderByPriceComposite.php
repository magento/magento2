<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\ResourceModel\Product;

/**
 * Class ProductProviderByPriceComposite
 */
class ProductProviderByPriceComposite implements ProductProviderByPriceInterface
{
    /**
     * @var ProductProviderByPriceInterface[]
     */
    private $productProviderByPrice;

    /**
     * @param ProductProviderByPriceInterface[] $productProviderByPrice
     */
    public function __construct($productProviderByPrice)
    {
        $this->productProviderByPrice = $productProviderByPrice;
    }

    /**
     * {@inheritdoc}
     */
    public function getSelect($productId)
    {
        $select = [];
        foreach ($this->productProviderByPrice as $productProvider) {
            $select = array_merge($select, $productProvider->getSelect($productId));
        }

        return $select;
    }
}
