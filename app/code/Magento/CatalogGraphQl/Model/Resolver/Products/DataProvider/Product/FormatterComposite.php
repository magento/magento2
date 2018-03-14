<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product;

use Magento\Catalog\Model\Product;

/**
 * Class implementing the composite pattern on @see FormatterInterface::format method
 */
class FormatterComposite implements FormatterInterface
{

    /**
     * @var FormatterInterface[]
     */
    private $formatterInstances = [];

    /**
     * @param FormatterInterface[] $formatterInstances
     */
    public function __construct(array $formatterInstances)
    {
        $this->formatterInstances = $formatterInstances;
    }

    /**
     * Format single product data from object to an array
     *
     * {@inheritdoc}
     */
    public function format(Product $product, array $productData = [])
    {
        foreach ($this->formatterInstances as $formatterInstance) {
            $productData = $formatterInstance->format($product, $productData);
        }

        return $productData;
    }
}
