<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model\IsProductSalableForRequestedQtyCondition;

use Magento\Framework\Exception\LocalizedException;
use Magento\InventorySalesApi\Api\IsProductSalableForRequestedQtyInterface;
use Magento\InventorySalesApi\Api\Data\ProductSalableResultInterface;
use Magento\InventorySalesApi\Api\Data\ProductSalableResultInterfaceFactory;
use Magento\InventorySalesApi\Api\Data\ProductSalabilityErrorInterfaceFactory;

/**
 * @inheritdoc
 */
class IsProductSalableForRequestedQtyConditionChain implements IsProductSalableForRequestedQtyInterface
{
    /**
     * @var IsProductSalableForRequestedQtyInterface[]
     */
    private $conditions;

    /**
     * @var IsProductSalableForRequestedQtyInterface[]
     */
    private $unrequiredConditions;

    /**
     * @var IsProductSalableForRequestedQtyInterface[]
     */
    private $requiredConditions;

    /**
     * @var ProductSalabilityErrorInterfaceFactory
     */
    private $productSalabilityErrorFactory;

    /**
     * @var ProductSalableResultInterfaceFactory
     */
    private $productSalableResultFactory;

    /**
     * @param ProductSalabilityErrorInterfaceFactory $productSalabilityErrorFactory
     * @param ProductSalableResultInterfaceFactory $productSalableResultFactory
     * @param array $conditions
     */
    public function __construct(
        ProductSalabilityErrorInterfaceFactory $productSalabilityErrorFactory,
        ProductSalableResultInterfaceFactory $productSalableResultFactory,
        array $conditions
    ) {
        $this->productSalabilityErrorFactory = $productSalabilityErrorFactory;
        $this->productSalableResultFactory = $productSalableResultFactory;
        $this->conditions = $conditions;
    }

    /**
     * This method validates conditions, splits them between required and not required and sorts the latter.
     * Required conditions are not sorted because changing their order may impact on the condition chain logic.
     */
    private function setConditions()
    {
        $this->validateConditions();

        $unrequiredConditions = array_filter(
            $this->conditions,
            function ($item) {
                return !isset($item['required']);
            }
        );
        $this->unrequiredConditions = array_column($this->sortConditions($unrequiredConditions), 'object');

        $requiredConditions = array_filter(
            $this->conditions,
            function ($item) {
                return isset($item['required']) && (bool) $item['required'];
            }
        );
        $this->requiredConditions = array_column($requiredConditions, 'object');
    }

    /**
     * @param array $this->conditions
     * @throws LocalizedException
     */
    private function validateConditions()
    {
        foreach ($this->conditions as $condition) {
            if (empty($condition['object'])) {
                throw new LocalizedException(__('Parameter "object" must be present.'));
            }

            if (empty($condition['required']) && empty($condition['sort_order'])) {
                throw new LocalizedException(__('Parameter "sort_order" must be present for urequired conditions.'));
            }

            // TODO Should we throw an exception when a required condition has a sort_order assigned?

            if (!$condition['object'] instanceof IsProductSalableForRequestedQtyInterface) {
                throw new LocalizedException(
                    __('Condition have to implement IsProductSalableForRequestedQtyInterface.')
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
    public function execute(string $sku, int $stockId, float $requestedQty): ProductSalableResultInterface
    {
        if (!empty($this->conditions) && empty($this->unrequiredConditions) && empty($this->requiredConditions)) {
            $this->setConditions();
        }

        // iterate over the required conditions: return error as soon as a condition fails
        $requiredConditionsErrors = [[]];
        foreach ($this->requiredConditions as $condition) {
            /** @var ProductSalableResultInterface $productSalableResult */
            $productSalableResult = $condition->execute($sku, $stockId, $requestedQty);
            if ($productSalableResult->isSalable()) {
                continue;
            }
            $requiredConditionsErrors[] = $productSalableResult->getErrors();
        }

        $requiredConditionsErrors = array_merge(...$requiredConditionsErrors);
        if (count($requiredConditionsErrors)) {
            return $this->productSalableResultFactory->create(['errors' => $requiredConditionsErrors]);
        }

        // iterate over not required conditions: return error if all conditions fail
        $unrequiredConditionsErrors = [[]];
        foreach ($this->unrequiredConditions as $condition) {
            /** @var ProductSalableResultInterface $productSalableResult */
            $productSalableResult = $condition->execute($sku, $stockId, $requestedQty);
            if ($productSalableResult->isSalable()) {
                return $this->productSalableResultFactory->create(['errors' => []]);
            }
            $unrequiredConditionsErrors[] = $productSalableResult->getErrors();
        }

        $unrequiredConditionsErrors = array_merge(...$unrequiredConditionsErrors);
        return $this->productSalableResultFactory->create(['errors' => $unrequiredConditionsErrors]);
    }
}
