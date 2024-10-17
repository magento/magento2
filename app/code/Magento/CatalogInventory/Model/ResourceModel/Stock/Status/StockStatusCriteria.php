<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogInventory\Model\ResourceModel\Stock\Status;

use Magento\CatalogInventory\Api\StockStatusCriteriaInterface;
use Magento\Framework\Data\AbstractCriteria;

/**
 * Class StockStatusCriteria
 */
class StockStatusCriteria extends AbstractCriteria implements StockStatusCriteriaInterface
{
    /**
     * @param string $mapper
     */
    public function __construct($mapper = '')
    {
        $this->mapperInterfaceName = $mapper ?: StockStatusCriteriaMapper::class;
        $this->data['initial_condition'] = [true];
    }

    /**
     * @inheritdoc
     */
    public function setScopeFilter($scope)
    {
        $this->data['website_filter'] = [$scope];
    }

    /**
     * @inheritdoc
     */
    public function setProductsFilter($products)
    {
        $this->data['products_filter'] = [$products];
    }

    /**
     * @inheritdoc
     */
    public function setQtyFilter($qty)
    {
        $this->data['qty_filter'] = [$qty];
    }

    /**
     * @inheritdoc
     */
    public function addCriteria(StockStatusCriteriaInterface $criteria)
    {
        $this->data[self::PART_CRITERIA_LIST]['list'][] = $criteria;
    }
}
