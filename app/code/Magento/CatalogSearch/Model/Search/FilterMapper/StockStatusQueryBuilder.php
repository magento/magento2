<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Model\Search\FilterMapper;

use Magento\CatalogInventory\Model\ResourceModel\Stock\Status as StockStatusResourceModel;
use Magento\Framework\DB\Select;
use Magento\Framework\Search\Adapter\Mysql\ConditionManager;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;

/**
 * Add stock status filter to Select
 */
class StockStatusQueryBuilder
{
    /**
     * @var StockStatusResourceModel
     */
    private $stockStatusResourceModel;

    /**
     * @var ConditionManager
     */
    private $conditionManager;

    /**
     * @var StockConfigurationInterface
     */
    private $stockConfiguration;

    /**
     * @var StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * @param StockStatusResourceModel $stockStatusResourceModel
     * @param ConditionManager $conditionManager
     * @param StockConfigurationInterface $stockConfiguration
     * @param StockRegistryInterface $stockRegistry
     */
    public function __construct(
        StockStatusResourceModel $stockStatusResourceModel,
        ConditionManager $conditionManager,
        StockConfigurationInterface $stockConfiguration,
        StockRegistryInterface $stockRegistry
    ) {
        $this->stockStatusResourceModel = $stockStatusResourceModel;
        $this->conditionManager = $conditionManager;
        $this->stockConfiguration = $stockConfiguration;
        $this->stockRegistry = $stockRegistry;
    }

    /**
     * Add stock filter to Select
     *
     * @param Select $select
     * @param string $mainTableAlias
     * @param string $stockTableAlias
     * @param string $joinField
     * @param mixed $values
     * @return Select
     */
    public function apply(
        Select $select,
        string $mainTableAlias,
        string $stockTableAlias,
        string $joinField,
        $values = null
    ): Select {
        $select->joinInner(
            [$stockTableAlias => $this->stockStatusResourceModel->getMainTable()],
            $this->conditionManager->combineQueries(
                [
                    sprintf('%s.product_id = %s.%s', $stockTableAlias, $mainTableAlias, $joinField),
                    $this->conditionManager->generateCondition(
                        sprintf('%s.website_id', $stockTableAlias),
                        '=',
                        $this->stockConfiguration->getDefaultScopeId()
                    ),
                    $values === null
                        ? ''
                        : $this->conditionManager->generateCondition(
                            sprintf('%s.stock_status', $stockTableAlias),
                            is_array($values) ? 'in' : '=',
                            $values
                        ),
                    $this->conditionManager->generateCondition(
                        sprintf('%s.stock_id', $stockTableAlias),
                        '=',
                        (int) $this->stockRegistry->getStock()->getStockId()
                    ),
                ],
                Select::SQL_AND
            ),
            []
        );

        return $select;
    }
}
