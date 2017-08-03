<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\CatalogInventory\Model\ResourceModel\Stock\Status;

use Magento\Framework\Data\AbstractCriteria;

/**
 * Class StockStatusCriteria
 * @since 2.0.0
 */
class StockStatusCriteria extends AbstractCriteria implements \Magento\CatalogInventory\Api\StockStatusCriteriaInterface
{
    /**
     * @param string $mapper
     * @since 2.0.0
     */
    public function __construct($mapper = '')
    {
        $this->mapperInterfaceName = $mapper ?: \Magento\CatalogInventory\Model\ResourceModel\Stock\Status\StockStatusCriteriaMapper::class;
        $this->data['initial_condition'] = true;
    }

    /**
     * @inheritdoc
     * @since 2.0.0
     */
    public function setScopeFilter($scope)
    {
        $this->data['website_filter'] = $scope;
    }

    /**
     * @inheritdoc
     * @since 2.0.0
     */
    public function setProductsFilter($products)
    {
        $this->data['products_filter'] = $products;
    }

    /**
     * @inheritdoc
     * @since 2.0.0
     */
    public function setQtyFilter($qty)
    {
        $this->data['qty_filter'] = $qty;
    }

    /**
     * @inheritdoc
     * @since 2.0.0
     */
    public function addCriteria(\Magento\CatalogInventory\Api\StockStatusCriteriaInterface $criteria)
    {
        $this->data[self::PART_CRITERIA_LIST]['list'][] = $criteria;
    }
}
