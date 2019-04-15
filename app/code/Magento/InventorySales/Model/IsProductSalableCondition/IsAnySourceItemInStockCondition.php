<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model\IsProductSalableCondition;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\SourceItemRepositoryInterface;
use Magento\InventoryCatalogApi\Model\GetProductTypesBySkusInterface;
use Magento\InventoryConfigurationApi\Model\GetAllowedProductTypesForSourceItemManagementInterface;
use Magento\InventorySalesApi\Api\IsProductSalableInterface;

/**
 * @inheritdoc
 */
class IsAnySourceItemInStockCondition implements IsProductSalableInterface
{
    /**
     * @var SourceItemRepositoryInterface
     */
    private $sourceItemRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var GetProductTypesBySkusInterface
     */
    private $getProductTypesBySkus;

    /**
     * @var GetAllowedProductTypesForSourceItemManagementInterface
     */
    private $getManageableTypes;

    /**
     * @param SourceItemRepositoryInterface $sourceItemRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param GetProductTypesBySkusInterface $getProductTypesBySkus
     * @param GetAllowedProductTypesForSourceItemManagementInterface $getManageableTypes
     */
    public function __construct(
        SourceItemRepositoryInterface $sourceItemRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        GetProductTypesBySkusInterface $getProductTypesBySkus,
        GetAllowedProductTypesForSourceItemManagementInterface $getManageableTypes
    ) {
        $this->sourceItemRepository = $sourceItemRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->getProductTypesBySkus = $getProductTypesBySkus;
        $this->getManageableTypes = $getManageableTypes;
    }

    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(string $sku, int $stockId): bool
    {
        if (!$this->isProductStockManageable($sku)) {
            return true;
        }
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(SourceItemInterface::SKU, $sku)
            ->addFilter(SourceItemInterface::STATUS, SourceItemInterface::STATUS_IN_STOCK)
            ->create();

        $sourceItems = $this->sourceItemRepository->getList($searchCriteria)->getItems();

        return (bool)count($sourceItems);
    }

    /**
     * @param string $sku
     *
     * @return bool
     */
    private function isProductStockManageable(string $sku): bool
    {
        return in_array(
            $this->getProductTypesBySkus->execute([$sku])[$sku],
            $this->getManageableTypes->execute()
        );
    }
}
