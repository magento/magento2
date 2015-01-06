<?php
/**
 * Product type transition manager
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Catalog\Model\Product;

use Magento\Catalog\Model\Product;

class TypeTransitionManager
{
    /**
     * List of compatible product types
     *
     * @var array
     */
    protected $compatibleTypes;

    /**
     * @param array $compatibleTypes
     */
    public function __construct(array $compatibleTypes)
    {
        $this->compatibleTypes = $compatibleTypes;
    }

    /**
     * Process given product and change its type if needed
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return void
     */
    public function processProduct(Product $product)
    {
        if (in_array($product->getTypeId(), $this->compatibleTypes)) {
            $product->setTypeInstance(null);
            $productTypeId = $product->hasIsVirtual() ? \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL : \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE;
            $product->setTypeId($productTypeId);
        }
    }
}
