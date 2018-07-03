<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model\IsProductSalableForRequestedQtyCondition;

use Magento\InventoryCatalogApi\Model\GetProductTypesBySkusInterface;
use Magento\InventoryConfigurationApi\Model\IsSourceItemManagementAllowedForProductTypeInterface;
use Magento\InventorySalesApi\Api\IsProductSalableForRequestedQtyInterface;
use Magento\InventorySalesApi\Api\Data\ProductSalableResultInterface;
use Magento\InventorySalesApi\Api\Data\ProductSalableResultInterfaceFactory;
use Magento\InventorySalesApi\Api\Data\ProductSalabilityErrorInterfaceFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\InventoryConfigurationApi\Exception\SkuIsNotAssignedToStockException;

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
     * @param ProductSalabilityErrorInterfaceFactory $productSalabilityErrorFactory
     * @param ProductSalableResultInterfaceFactory $productSalableResultFactory
     * @param GetProductTypesBySkusInterface $getProductTypesBySkus
     * @param IsSourceItemManagementAllowedForProductTypeInterface $isSourceItemManagementAllowedForProductType
     * @param array $conditions
     */
    public function __construct(
        ProductSalabilityErrorInterfaceFactory $productSalabilityErrorFactory,
        ProductSalableResultInterfaceFactory $productSalableResultFactory,
        GetProductTypesBySkusInterface $getProductTypesBySkus,
        IsSourceItemManagementAllowedForProductTypeInterface $isSourceItemManagementAllowedForProductType,
        array $conditions
    ) {
        $this->productSalabilityErrorFactory = $productSalabilityErrorFactory;
        $this->productSalableResultFactory = $productSalableResultFactory;
        $this->getProductTypesBySkus = $getProductTypesBySkus;
        $this->isSourceItemManagementAllowedForProductType = $isSourceItemManagementAllowedForProductType;
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

        try {
            $requiredConditionsErrors = $this->processRequiredConditions($sku, $stockId, $requestedQty);
            if (count($requiredConditionsErrors)) {
                return $this->productSalableResultFactory->create(['errors' => $requiredConditionsErrors]);
            }

            $sufficientConditionsErrors = $this->processSufficientConditions($sku, $stockId, $requestedQty);
            if (count($sufficientConditionsErrors)) {
                return $this->productSalableResultFactory->create(['errors' => $sufficientConditionsErrors]);
            }
        } catch (SkuIsNotAssignedToStockException $e) {
            $errors = [
                $this->productSalabilityErrorFactory->create([
                    'code' => 'requested-sku-is-not-assigned-to-given-stock',
                    'message' => __('The requested sku is not assigned to given stock.')
                ])
            ];
            return $this->productSalableResultFactory->create(['errors' => $errors]);
        }

        return $this->productSalableResultFactory->create(['errors' => []]);
    }

    /**
     * Required conditions
     *
     * Iterate over required conditions: return error if at least one of them doesn't passed
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
        $requiredConditionsErrors = array_merge(...$requiredConditionsErrors);

        return $requiredConditionsErrors;
    }

    /**
     * Sufficient conditions ordered by priority.
     *
     * Iterate over sufficient conditions: return true if one of them is Salable
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
                return [];
            }
            $sufficientConditionsErrors[] = $productSalableResult->getErrors();
        }
        $sufficientConditionsErrors = array_merge(...$sufficientConditionsErrors);

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
