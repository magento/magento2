<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryMassSourceAssign\Model;

use Magento\Catalog\Model\Product\Type;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory;
use Magento\InventoryCatalogApi\Model\GetProductTypesBySkusInterface;
use Magento\InventoryConfigurationApi\Model\IsSourceItemManagementAllowedForProductTypeInterface;

/**
 * Builds a SourceItems array from a list of SKU and SOURCE_CODES
 * SourceItems will be initialized with zero quantity and out-of-stock status
 *
 * Products not eligible for source assignment will be automatically filtered
 */
class SourceItemsBuilder
{
    /**
     * @var SourceItemInterfaceFactory
     */
    private $sourceItemInterfaceFactory;

    /**
     * @var GetProductTypesBySkusInterface
     */
    private $getProductTypesBySkus;

    /**
     * @var IsSourceItemManagementAllowedForProductTypeInterface
     */
    private $isSourceItemManagementAllowedForProductType;

    /**
     * @param SourceItemInterfaceFactory $sourceItemInterfaceFactory
     * @param GetProductTypesBySkusInterface $getProductTypesBySkus
     * @param IsSourceItemManagementAllowedForProductTypeInterface $isSourceItemManagementAllowedForProductType
     * @SuppressWarnings(PHPMD.LongVariables)
     */
    public function __construct(
        SourceItemInterfaceFactory $sourceItemInterfaceFactory,
        GetProductTypesBySkusInterface $getProductTypesBySkus,
        IsSourceItemManagementAllowedForProductTypeInterface $isSourceItemManagementAllowedForProductType
    ) {
        $this->sourceItemInterfaceFactory = $sourceItemInterfaceFactory;
        $this->getProductTypesBySkus = $getProductTypesBySkus;
        $this->isSourceItemManagementAllowedForProductType = $isSourceItemManagementAllowedForProductType;
    }

    /**
     * @param array $skus
     * @param array $sourceCodes
     * @return SourceItemInterface[]
     */
    public function create(array $skus, array $sourceCodes): array
    {
        $sourceItems = [];
        $types = $this->getProductTypesBySkus->execute($skus);

        foreach ($types as $sku => $type) {
            if ($this->isSourceItemManagementAllowedForProductType->execute($type)) {
                foreach ($sourceCodes as $sourceCode) {
                    $sourceItems[] = $this->sourceItemInterfaceFactory->create(['data' => [
                        SourceItemInterface::SKU => $sku,
                        SourceItemInterface::SOURCE_CODE => $sourceCode,
                        SourceItemInterface::QUANTITY => 0,
                        SourceItemInterface::STATUS => SourceItemInterface::STATUS_OUT_OF_STOCK,
                    ]]);
                }
            }
        }

        return $sourceItems;
    }
}
