<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Data\CollectionModifierInterface;

/**
 * Add visibility to product collection
 */
class ProductVisibilityCondition implements CollectionModifierInterface
{
    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    private $productVisibility;

    /**
     * ProductVisibilityCondition constructor.
     * @param \Magento\Catalog\Model\Product\Visibility $productVisibility
     */
    public function __construct(\Magento\Catalog\Model\Product\Visibility $productVisibility)
    {
        $this->productVisibility = $productVisibility;
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @return void
     */
    public function apply(AbstractDb $collection)
    {
        $collection->setVisibility($this->productVisibility->getVisibleInCatalogIds());
    }
}
