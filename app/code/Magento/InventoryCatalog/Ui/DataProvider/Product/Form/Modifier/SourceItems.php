<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Inventory\Model\IsSourceItemsManagementAllowedForProductTypeInterface;
use Magento\Inventory\Model\ResourceModel\Source as SourceResourceModel;
use Magento\Inventory\Model\ResourceModel\SourceItem\Collection;
use Magento\Inventory\Model\ResourceModel\SourceItem\CollectionFactory;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryCatalog\Model\IsSingleSourceModeInterface;

/**
 * Product form modifier. Add to form source data
 */
class SourceItems extends AbstractModifier
{
    /**
     * @var IsSourceItemsManagementAllowedForProductTypeInterface
     */
    private $isSourceItemsManagementAllowedForProductType;

    /**
     * @var IsSingleSourceModeInterface
     */
    private $isSingleSourceMode;

    /**
     * @var LocatorInterface
     */
    private $locator;

    /**
     * @var CollectionFactory
     */
    private $sourceItemCollectionFactory;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param IsSourceItemsManagementAllowedForProductTypeInterface $isSourceItemsManagementAllowedForProductType
     * @param IsSingleSourceModeInterface $isSingleSourceMode
     * @param LocatorInterface $locator
     * @param CollectionFactory $sourceItemCollectionFactory
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        IsSourceItemsManagementAllowedForProductTypeInterface $isSourceItemsManagementAllowedForProductType,
        IsSingleSourceModeInterface $isSingleSourceMode,
        LocatorInterface $locator,
        CollectionFactory $sourceItemCollectionFactory,
        ResourceConnection $resourceConnection
    ) {
        $this->isSourceItemsManagementAllowedForProductType = $isSourceItemsManagementAllowedForProductType;
        $this->isSingleSourceMode = $isSingleSourceMode;
        $this->locator = $locator;
        $this->sourceItemCollectionFactory = $sourceItemCollectionFactory;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @inheritdoc
     */
    public function modifyData(array $data)
    {
        $product = $this->locator->getProduct();

        if ($this->isSingleSourceMode->execute() === true
            || $this->isSourceItemsManagementAllowedForProductType->execute($product->getTypeId()) === false
            || null === $product->getId()
        ) {
            return $data;
        }

        $data[$product->getId()]['sources']['assigned_sources'] = $this->getSourceItemsData();
        return $data;
    }

    /**
     * @return array
     */
    private function getSourceItemsData(): array
    {
        $product = $this->locator->getProduct();

        /** @var Collection $collection */
        $collection = $this->sourceItemCollectionFactory->create();
        $collection->addFilter(SourceItemInterface::SKU, $product->getSku());
        $collection->join(
            ['s' => $this->resourceConnection->getTableName(SourceResourceModel::TABLE_NAME_SOURCE)],
            sprintf('s.%s = main_table.%s', SourceInterface::SOURCE_CODE, SourceItemInterface::SOURCE_CODE),
            ['source_name' => SourceInterface::NAME]
        );

        $sourceItemsData = [];
        foreach ($collection->getData() as $row) {
            $sourceItemsData[] = [
                SourceItemInterface::SOURCE_CODE => $row[SourceItemInterface::SOURCE_CODE],
                SourceItemInterface::QUANTITY => $row[SourceItemInterface::QUANTITY],
                SourceItemInterface::STATUS => $row[SourceItemInterface::STATUS],
                SourceInterface::NAME => $row['source_name'],
            ];
        }
        return $sourceItemsData;
    }

    /**
     * @inheritdoc
     */
    public function modifyMeta(array $meta)
    {
        $product = $this->locator->getProduct();

        if ($this->isSingleSourceMode->execute() === true
            || $this->isSourceItemsManagementAllowedForProductType->execute($product->getTypeId()) === false) {
            return $meta;
        }

        $meta['sources'] = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'visible' => 1,
                    ],
                ],
            ],
        ];
        return $meta;
    }
}
