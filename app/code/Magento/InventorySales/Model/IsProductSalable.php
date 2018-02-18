<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\InventoryConfiguration\Model\StockItemConditionInterface;
use Magento\InventorySalesApi\Api\IsProductSalableInterface;

/**
 * @inheritdoc
 */
class IsProductSalable implements IsProductSalableInterface
{
    /**
     * @var StockItemConditionInterface[]
     */
    private $conditions;

    /**
     * IsProductSalable constructor.
     * @param array $conditions
     * @throws LocalizedException
     */
    public function __construct(
        array $conditions
    ) {
        $this->conditions = $this->prepareConditions($conditions);
    }

    /**
     * @inheritdoc
     */
    public function execute(string $sku, int $stockId): bool
    {
        foreach ($this->conditions as $condition) {
            if ($condition->match($sku, $stockId) === true) {
                return true;
            }
        }

        return false;
    }

    /**
     * Sort conditions according to sort passed sort order
     *
     * @param array $conditions
     * @return array
     * @throws LocalizedException
     */
    private function prepareConditions(array $conditions)
    {
        usort($conditions, function (array $condition_left, array $condition_right) {
            if ($condition_left['sort_order'] == $condition_right['sort_order']) {
                return 0;
            }

            return ($condition_left['sort_order'] < $condition_right['sort_order']) ? -1 : 1;
        });

        $conditionsList = [];
        foreach ($conditions as $item) {
            if (!$item['condition'] instanceof StockItemConditionInterface) {
                throw new LocalizedException(
                    __('Condition have to implement StockItemConditionInterface.')
                );
            } else {
                $conditionsList[] = $item['condition'];
            }
        }

        return $conditionsList;
    }
}
