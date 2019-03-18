<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model\IsProductSalableCondition;

use Magento\InventorySalesApi\Api\IsProductSalableInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\InventoryConfigurationApi\Exception\SkuIsNotAssignedToStockException;

/**
 * @inheritdoc
 */
class IsProductSalableConditionChain implements IsProductSalableInterface
{
    /**
     * @var IsProductSalableInterface[]
     */
    private $unrequiredConditions;

    /**
     * @var IsProductSalableInterface[]
     */
    private $requiredConditions;

    /**
     * @param array $conditions
     */
    public function __construct(
        array $conditions
    ) {
        $this->setConditions($conditions);
    }

    /**
     * @param array $conditions
     */
    private function setConditions(array $conditions)
    {
        $this->validateConditions($conditions);

        $unrequiredConditions = array_filter(
            $conditions,
            function ($item) {
                return !isset($item['required']);
            }
        );
        $this->unrequiredConditions = array_column($this->sortConditions($unrequiredConditions), 'object');

        $requiredConditions = array_filter(
            $conditions,
            function ($item) {
                return isset($item['required']) && (bool) $item['required'];
            }
        );
        $this->requiredConditions = array_column($requiredConditions, 'object');
    }

    /**
     * @param array $conditions
     * @throws LocalizedException
     */
    private function validateConditions(array $conditions)
    {
        foreach ($conditions as $condition) {
            if (empty($condition['object'])) {
                throw new LocalizedException(__('Parameter "object" must be present.'));
            }

            if (empty($condition['required']) && empty($condition['sort_order'])) {
                throw new LocalizedException(__('Parameter "sort_order" must be present for unrequired conditions.'));
            }

            if (!$condition['object'] instanceof IsProductSalableInterface) {
                throw new LocalizedException(
                    __('Condition has to implement IsProductSalableInterface.')
                );
            }
        }
    }

    /**
     * @param array $conditions
     * @return array
     */
    private function sortConditions(array $conditions)
    {
        usort($conditions, function (array $conditionLeft, array $conditionRight) {
            if ($conditionLeft['sort_order'] == $conditionRight['sort_order']) {
                return 0;
            }
            return ($conditionLeft['sort_order'] < $conditionRight['sort_order']) ? -1 : 1;
        });
        return $conditions;
    }

    /**
     * @inheritdoc
     */
    public function execute(string $sku, int $stockId): bool
    {
        try {
            foreach ($this->requiredConditions as $requiredCondition) {
                if ($requiredCondition->execute($sku, $stockId) === false) {
                    return false;
                }
            }
            foreach ($this->unrequiredConditions as $unrequiredCondition) {
                if ($unrequiredCondition->execute($sku, $stockId) === true) {
                    return true;
                }
            }
        } catch (SkuIsNotAssignedToStockException $e) {
            return false;
        }

        return false;
    }
}
