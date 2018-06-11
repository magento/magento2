<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model\IsProductSalableForRequestedQtyCondition;

use Magento\Framework\Exception\LocalizedException;
use Magento\InventoryCatalogApi\Model\GetProductTypesBySkusInterface;
use Magento\InventoryConfigurationApi\Model\IsSourceItemManagementAllowedForProductTypeInterface;
use Magento\InventorySalesApi\Api\IsProductSalableForRequestedQtyInterface;
use Magento\InventorySalesApi\Api\Data\ProductSalableResultInterface;
use Magento\InventorySalesApi\Api\Data\ProductSalableResultInterfaceFactory;
use Magento\InventorySalesApi\Api\Data\ProductSalabilityErrorInterfaceFactory;
use Magento\InventorySalesApi\Model\GetStockItemDataInterface;

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
     * @var GetProductTypesBySkusInterface
     */
    private $getProductTypesBySkus;

    /**
     * @var IsSourceItemManagementAllowedForProductTypeInterface
     */
    private $isSourceItemManagementAllowedForProductType;

    /**
     * @var GetStockItemDataInterface
     */
    private $getStockItemData;

    /**
     * @param ProductSalabilityErrorInterfaceFactory $productSalabilityErrorFactory
     * @param ProductSalableResultInterfaceFactory $productSalableResultFactory
     * @param GetProductTypesBySkusInterface $getProductTypesBySkus
     * @param IsSourceItemManagementAllowedForProductTypeInterface $isSourceItemManagementAllowedForProductType
     * @param GetStockItemDataInterface $getStockItemData
     * @param array $conditions
     */
    public function __construct(
        ProductSalabilityErrorInterfaceFactory $productSalabilityErrorFactory,
        ProductSalableResultInterfaceFactory $productSalableResultFactory,
        GetProductTypesBySkusInterface $getProductTypesBySkus,
        IsSourceItemManagementAllowedForProductTypeInterface $isSourceItemManagementAllowedForProductType,
        GetStockItemDataInterface $getStockItemData,
        array $conditions
    ) {
        $this->productSalabilityErrorFactory = $productSalabilityErrorFactory;
        $this->productSalableResultFactory = $productSalableResultFactory;
        $this->getProductTypesBySkus = $getProductTypesBySkus;
        $this->isSourceItemManagementAllowedForProductType = $isSourceItemManagementAllowedForProductType;
        $this->conditions = $conditions;
        $this->getStockItemData = $getStockItemData;
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
    private function sortConditions(array $conditions): array
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
        $this->validateProductType($sku);

        if (!empty($this->conditions) && empty($this->unrequiredConditions) && empty($this->requiredConditions)) {
            $this->setConditions();
        }

        $stockItemData = $this->getStockItemData->execute($sku, $stockId);
        if (null === $stockItemData) {
            $errors = [
                $this->productSalabilityErrorFactory->create([
                    'code' => 'is_salable_with_reservations-no_data',
                    'message' => __('The requested sku is not assigned to given stock.')
                ])
            ];
            return $this->productSalableResultFactory->create(['errors' => $errors]);
        }

        $requiredConditionsErrors = $this->processRequiredConditions($sku, $stockId, $requestedQty);
        $requiredConditionsErrors = array_merge(...$requiredConditionsErrors);
        if (count($requiredConditionsErrors)) {
            return $this->productSalableResultFactory->create(['errors' => $requiredConditionsErrors]);
        }

        $sufficientConditionsErrors = $this->processSufficientConditions($sku, $stockId, $requestedQty);
        $sufficientConditionsErrors = array_merge(...$sufficientConditionsErrors);
        if (count($sufficientConditionsErrors)) {
            return $this->productSalableResultFactory->create(['errors' => $sufficientConditionsErrors]);
        }

        return $this->productSalableResultFactory->create(['errors' => []]);
    }

    /**
     * Required conditions
     *
     * @param string $sku
     * @param int $stockId
     * @param float $requestedQty
     * @return array
     * @throws LocalizedException
     */
    private function processRequiredConditions(string $sku, int $stockId, float $requestedQty): array
    {
        $requiredConditionsErrors = [[]];
        foreach ($this->requiredConditions as $condition) {
            /** @var ProductSalableResultInterface $productSalableResult */
            $productSalableResult = $condition->execute($sku, $stockId, $requestedQty);
            if ($productSalableResult->isSalable()) {
                continue;
            }
            $requiredConditionsErrors[] = $productSalableResult->getErrors();
        }

        return $requiredConditionsErrors;
    }

    /**
     * Sufficient conditions ordered by priority.
     *
     * @param string $sku
     * @param int $stockId
     * @param float $requestedQty
     * @return array
     * @throws LocalizedException
     */
    private function processSufficientConditions(string $sku, int $stockId, float $requestedQty): array
    {
        $sufficientConditionsErrors = [[]];
        foreach ($this->unrequiredConditions as $condition) {
            /** @var ProductSalableResultInterface $productSalableResult */
            $productSalableResult = $condition->execute($sku, $stockId, $requestedQty);
            if ($productSalableResult->isSalable()) {
                return [[]];
            }
            $sufficientConditionsErrors[] = $productSalableResult->getErrors();
        }

        return $sufficientConditionsErrors;
    }

    /**
     * @param string $sku
     * @throws LocalizedException
     */
    private function validateProductType(string $sku): void
    {
        $productType = $this->getProductTypesBySkus->execute([$sku])[$sku];
        if (false === $this->isSourceItemManagementAllowedForProductType->execute($productType)) {
            throw new LocalizedException(
                __('Can\'t check requested quantity for products without Source Items support.')
            );
        }
    }
}
