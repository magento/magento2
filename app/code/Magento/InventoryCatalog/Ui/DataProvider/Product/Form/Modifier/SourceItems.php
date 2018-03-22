<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\CatalogInventory\Model\Configuration;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Inventory\Model\ResourceModel\Source as SourceResourceModel;
use Magento\Inventory\Model\ResourceModel\SourceItem\Collection;
use Magento\Inventory\Model\ResourceModel\SourceItem\CollectionFactory;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryCatalog\Api\DefaultStockProviderInterface;
use Magento\InventoryCatalog\Model\IsSingleSourceModeInterface;
use Magento\InventoryConfiguration\Model\IsSourceItemsAllowedForProductTypeInterface;
use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;

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
     * Provides "Manage Stock" global config value.
     *
     * @var ScopeConfigInterface
     */
    private $config;

    /**
     * Retrieves stock item for given product.
     *
     * @var GetStockItemConfigurationInterface
     */
    private $getStockItemConfiguration;

    /**
     * Provides default stock id for current website in order to get correct stock item for product.
     *
     * @var DefaultStockProviderInterface
     */
    private $defaultStockProvider;

    /**
     * @param IsSourceItemsAllowedForProductTypeInterface $isSourceItemsAllowedForProductType
     * @param IsSingleSourceModeInterface $isSingleSourceMode
     * @param LocatorInterface $locator
     * @param CollectionFactory $sourceItemCollectionFactory
     * @param ResourceConnection $resourceConnection
     * @param ScopeConfigInterface $config
     * @param GetStockItemConfigurationInterface $getStockItemConfiguration
     * @param DefaultStockProviderInterface $defaultStockProvider
     */
    public function __construct(
        IsSourceItemsAllowedForProductTypeInterface $isSourceItemsAllowedForProductType,
        IsSingleSourceModeInterface $isSingleSourceMode,
        LocatorInterface $locator,
        CollectionFactory $sourceItemCollectionFactory,
        ResourceConnection $resourceConnection,
        ScopeConfigInterface $config,
        GetStockItemConfigurationInterface $getStockItemConfiguration,
        DefaultStockProviderInterface $defaultStockProvider
    ) {
        $this->isSourceItemsAllowedForProductType = $isSourceItemsAllowedForProductType;
        $this->isSingleSourceMode = $isSingleSourceMode;
        $this->locator = $locator;
        $this->sourceItemCollectionFactory = $sourceItemCollectionFactory;
        $this->resourceConnection = $resourceConnection;
        $this->config = $config;
        $this->getStockItemConfiguration = $getStockItemConfiguration;
        $this->defaultStockProvider = $defaultStockProvider;
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
        $isMangeStock = $this->isMangeStock($product);
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
                                        'visible' => $isMangeStock,
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
                                'visible' => $isMangeStock,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        return $meta;
    }

    /**
     * Get "isManageStock" from product, and fall back to global config in case of new product.
     *
     * @param ProductInterface $product
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function isMangeStock(ProductInterface $product): bool
    {
        $stockId = $this->defaultStockProvider->getId();
        if ($product->getSku()) {
            $itemConfiguration = $this->getStockItemConfiguration->execute($product->getSku(), $stockId);
            if ($itemConfiguration) {
                return $itemConfiguration->isUseConfigManageStock()
                    ? (bool)$this->config->getValue(Configuration::XML_PATH_MANAGE_STOCK)
                    : $itemConfiguration->isManageStock();
            }
        }

        return (bool)$this->config->getValue(Configuration::XML_PATH_MANAGE_STOCK);
    }
}
