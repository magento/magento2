<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryIndexer\Model\StockCondition;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\LocalizedException;

/**
 * Chain of stock conditions.
 */
class GetStockConditionChain implements GetStockConditionInterface
{
    /**
     * @var GetStockConditionInterface[]
     */
    private $stockConditions;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param array $stockConditions
     * @param ResourceConnection $resourceConnection
     *
     * @throws LocalizedException
     */
    public function __construct(
        array $stockConditions = [],
        ResourceConnection $resourceConnection
    ) {
        foreach ($stockConditions as $stockCondition) {
            if (!$stockCondition instanceof GetStockConditionInterface) {
                throw new LocalizedException(
                    __('Stock Condition must implement GetStockConditionInterface.')
                );
            }
        }
        $this->stockConditions = $stockConditions;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @inheritdoc
     */
    public function execute(): string
    {
        $conditionString = '';
        $lastElement = end($this->stockConditions);
        foreach ($this->stockConditions as $key => $stockCondition) {
            $conditionString .= $stockCondition->execute();
            if ($lastElement !== $stockCondition) {
                $conditionString .= ' OR ';
            }
        }

        $isSalableExpression = $this->resourceConnection->getConnection()->getCheckSql($conditionString, 1, 0);

        return (string)$isSalableExpression;
    }
}
