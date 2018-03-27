<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\App\ResourceConnection;
use Magento\Inventory\Model\ResourceModel\Source as SourceResourceModel;
use Magento\Inventory\Model\ResourceModel\SourceItem\Collection;
use Magento\Inventory\Model\ResourceModel\SourceItem\CollectionFactory;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryCatalog\Model\CanManageSourceItemsBySku;
use Magento\InventoryCatalog\Model\IsSingleSourceModeInterface;
use Magento\InventoryConfiguration\Model\IsSourceItemsAllowedForProductTypeInterface;

/**
 * Product form modifier. Add to form source data
 */
class SourceItems extends AbstractModifier
{
    /**
     * @var IsSourceItemsAllowedForProductTypeInterface
     */
    private $isSourceItemsAllowedForProductType;

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
     * @var CanManageSourceItemsBySku
     */
    private $canManageSourceItemsBySku;

    /**
     * @param IsSourceItemsAllowedForProductTypeInterface $isSourceItemsAllowedForProductType
     * @param IsSingleSourceModeInterface $isSingleSourceMode
     * @param LocatorInterface $locator
     * @param CollectionFactory $sourceItemCollectionFactory
     * @param ResourceConnection $resourceConnection
     * @param CanManageSourceItemsBySku $canManageSourceItemsBySku
     */
    public function __construct(
        IsSourceItemsAllowedForProductTypeInterface $isSourceItemsAllowedForProductType,
        IsSingleSourceModeInterface $isSingleSourceMode,
        LocatorInterface $locator,
        CollectionFactory $sourceItemCollectionFactory,
        ResourceConnection $resourceConnection,
        CanManageSourceItemsBySku $canManageSourceItemsBySku
    ) {
        $this->isSourceItemsAllowedForProductType = $isSourceItemsAllowedForProductType;
        $this->isSingleSourceMode = $isSingleSourceMode;
        $this->locator = $locator;
        $this->sourceItemCollectionFactory = $sourceItemCollectionFactory;
        $this->resourceConnection = $resourceConnection;
        $this->canManageSourceItemsBySku = $canManageSourceItemsBySku;
    }

    /**
     * @inheritdoc
     */
    public function modifyData(array $data)
    {
        $product = $this->locator->getProduct();

        if ($this->isSingleSourceMode->execute() === true
            || $this->isSourceItemsAllowedForProductType->execute($product->getTypeId()) === false
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
            ['source_name' => SourceInterface::NAME, 'source_status' => SourceInterface::ENABLED]
        );

        $sourceItemsData = [];
        foreach ($collection->getData() as $row) {
            $sourceItemsData[] = [
                SourceItemInterface::SOURCE_CODE => $row[SourceItemInterface::SOURCE_CODE],
                SourceItemInterface::QUANTITY => $row[SourceItemInterface::QUANTITY],
                SourceItemInterface::STATUS => $row[SourceItemInterface::STATUS],
                SourceInterface::NAME => $row['source_name'],
                'source_status' => $row['source_status'],
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
            || $this->isSourceItemsAllowedForProductType->execute($product->getTypeId()) === false) {
            return $meta;
        }

        $canMangeSourceItems = $this->canManageSourceItemsBySku->execute($product->getSku());
        $meta['sources'] = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'visible' => 1,
                    ],
                ],
            ],
            'children' => [
                'assign_sources_container' => [
                    'children' => [
                        'assign_sources_button' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'visible' => $canMangeSourceItems,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'assigned_sources' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'visible' => $canMangeSourceItems,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        return $meta;
    }
}
