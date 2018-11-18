<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Review\Model\Api\SearchCriteria\CollectionProcessor\FilterProcessor;

use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Framework\Api\Filter;
use Magento\Framework\Api\SearchCriteria\CollectionProcessor\FilterProcessor\CustomFilterInterface;
use Magento\Framework\Data\Collection\AbstractDb;

/**
 * Class ReviewSkuFilter
 */
class ReviewSkuFilter implements CustomFilterInterface
{
    /**
     * Product resource
     *
     * @var ProductResource
     */
    private $productResource;

    /**
     * ReviewSkuFilter constructor
     *
     * @param ProductResource $productResource
     */
    public function __construct(
        ProductResource $productResource
    ) {
        $this->productResource = $productResource;
    }

    /**
     * Apply sku Filter to Review Collection
     *
     * @param Filter $filter
     * @param AbstractDb $collection
     * @return bool Whether the filter is applied
     */
    public function apply(Filter $filter, AbstractDb $collection)
    {
        $productId = $this->productResource->getIdBySku($filter->getValue());

        /** @var \Magento\Review\Model\ResourceModel\Review\Collection $collection */
        $collection->addEntityFilter('product', $productId);

        return true;
    }
}
