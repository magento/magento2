<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Model\ResourceModel\Stock;

use Magento\Framework\Data\AbstractCriteria;

/**
 * Class StockCriteria
 * @since 2.0.0
 */
class StockCriteria extends AbstractCriteria implements \Magento\CatalogInventory\Api\StockCriteriaInterface
{
    /**
     * @param string $mapper
     * @since 2.0.0
     */
    public function __construct($mapper = '')
    {
        $this->mapperInterfaceName =
            $mapper ?: \Magento\CatalogInventory\Model\ResourceModel\Stock\StockCriteriaMapper::class;
        $this->data['initial_condition'] = true;
    }

    /**
     * @inheritdoc
     * @since 2.0.0
     */
    public function setScopeFilter($scope)
    {
        $this->data['website_filter'] = $scope;
        return true;
    }

    /**
     * Add Criteria object
     *
     * @param \Magento\CatalogInventory\Api\StockCriteriaInterface $criteria
     * @return bool
     * @since 2.0.0
     */
    public function addCriteria(\Magento\CatalogInventory\Api\StockCriteriaInterface $criteria)
    {
        $this->data[self::PART_CRITERIA_LIST]['list'][] = $criteria;
        return true;
    }
}
