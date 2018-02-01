<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Model\ResourceModel\IsSalableCondition;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\LocalizedException;

/**
 * Chain of stock conditions.
 */
class GetStockConditionChain implements GetIsSalableConditionInterface
{
    /**
     * @var GetIsSalableConditionInterface[]
     */
    private $getIsSalableConditions = [];

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param ResourceConnection $resourceConnection
     * @param array $getIsSalableConditions
     *
     * @throws LocalizedException
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        array $getIsSalableConditions = []
    ) {
        foreach ($getIsSalableConditions as $getIsSalableCondition) {
            if (!$getIsSalableCondition instanceof GetIsSalableConditionInterface) {
                throw new LocalizedException(
                    __('Condition must implement GetIsSalableConditionInterface')
                );
            }
        }
        $this->resourceConnection = $resourceConnection;
        $this->getIsSalableConditions = $getIsSalableConditions;
    }

    /**
     * @inheritdoc
     */
    public function execute(): string
    {
        if (empty($this->getIsSalableConditions)) {
            return '';
        }

        $conditionStrings = [];
        foreach ($this->getIsSalableConditions as $getIsSalableCondition) {
            $conditionStrings[] = $getIsSalableCondition->execute();
        }

        $conditionString = (string)$this->resourceConnection->getConnection()->getCheckSql(
            implode($conditionStrings, 'OR'),
            1,
            0
        );
        return 'MAX(' . $conditionString . ')';
    }
}
