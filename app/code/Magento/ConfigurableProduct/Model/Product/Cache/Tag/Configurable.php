<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model\Product\Cache\Tag;

use Magento\Framework\App\Cache\Tag\StrategyInterface;

/**
 * Add parent invalidation tags
 */
class Configurable implements StrategyInterface
{
    /**
     *  Configurable product type resource
     *
     * @var \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable
     */
    private $catalogProductTypeConfigurable;

    /**
     * @param \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $catalogProductTypeConfigurable
     */
    public function __construct(
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $catalogProductTypeConfigurable
    ) {
        $this->catalogProductTypeConfigurable = $catalogProductTypeConfigurable;
    }

    /**
     * {@inheritdoc}
     */
    public function getTags($object)
    {
        if (!is_object($object)) {
            throw new \InvalidArgumentException('Provided argument is not an object');
        }

        if (!($object instanceof \Magento\Catalog\Model\Product)) {
            throw new \InvalidArgumentException('Provided argument must be a product');
        }

        $result = $object->getIdentities();

        foreach ($this->catalogProductTypeConfigurable->getParentIdsByChild($object->getId()) as $parentId) {
            $result[] = \Magento\Catalog\Model\Product::CACHE_TAG . '_' . $parentId;
        }
        return $result;
    }
}
