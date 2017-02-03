<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Model\Product;

/**
 * Class VariationMediaAttributes. Return media attributes allowed for variations
 */
class VariationMediaAttributes
{
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var array
     */
    protected $mediaAttributes;

    /**
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     */
    public function __construct(\Magento\Catalog\Model\ProductFactory $productFactory)
    {
        $this->productFactory = $productFactory;
    }

    /**
     * Get media attributes for Configurable variation
     *
     * @return array
     */
    public function getMediaAttributes()
    {
        if (null === $this->mediaAttributes) {
            $this->mediaAttributes = $this->getProduct()->getMediaAttributes();
        }
        return $this->mediaAttributes;
    }

    /**
     * Get product container for Simple product
     *
     * @return \Magento\Catalog\Model\Product
     */
    private function getProduct()
    {
        return $this->productFactory->create()->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE);
    }
}
