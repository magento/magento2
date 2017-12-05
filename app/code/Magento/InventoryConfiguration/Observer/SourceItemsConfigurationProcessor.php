<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfiguration\Observer;

use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\InputException;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryConfiguration\Model\SourceItemConfigurationFactory;
use Magento\InventoryConfigurationApi\Api\Data\SourceItemConfigurationInterface;
use Magento\InventoryConfigurationApi\Api\DeleteSourceItemConfigurationInterface;
use Magento\InventoryConfigurationApi\Api\GetSourceItemConfigurationInterface;
use Magento\InventoryConfigurationApi\Api\SourceItemConfigurationsSaveInterface;

/**
 * Process source item configuration.
 */
class SourceItemsConfigurationProcessor
{
    /**
     * @var GetSourceItemConfigurationInterface
     */
    private $getSourceItemConfiguration;

    /**
     * @var SourceItemConfigurationFactory
     */
    private $sourceItemConfigurationFactory;

    /**
     * @var SourceItemConfigurationsSaveInterface
     */
    private $sourceItemConfigurationSave;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var DeleteSourceItemConfigurationInterface
     */
    private $sourceItemConfigurationDelete;

    /**
     * @param SourceItemConfigurationFactory $sourceItemConfigurationFactory
     * @param SourceItemConfigurationsSaveInterface $sourceItemConfigurationSave
     * @param DeleteSourceItemConfigurationInterface $sourceItemConfigurationDelete
     * @param GetSourceItemConfigurationInterface $getSourceItemConfiguration
     * @param DataObjectHelper $dataObjectHelper
     */
    public function __construct(
        SourceItemConfigurationFactory $sourceItemConfigurationFactory,
        SourceItemConfigurationsSaveInterface $sourceItemConfigurationSave,
        DeleteSourceItemConfigurationInterface $sourceItemConfigurationDelete,
        GetSourceItemConfigurationInterface $getSourceItemConfiguration,
        DataObjectHelper $dataObjectHelper
    ) {
        $this->sourceItemConfigurationSave = $sourceItemConfigurationSave;
        $this->sourceItemConfigurationFactory = $sourceItemConfigurationFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->sourceItemConfigurationDelete = $sourceItemConfigurationDelete;
        $this->getSourceItemConfiguration = $getSourceItemConfiguration;
    }

    /**
     * @param string $sku
     * @param array $sourceItemsData
     * @return void
     * @throws InputException
     */
    public function process($sku, array $sourceItemsData)
    {
        $sourceItemsConfigsForDelete = $this->getCurrentSourceItemsMap($sku, $sourceItemsData);
        $sourceItemsConfigsForSave = [];

        foreach ($sourceItemsData as $sourceItemData) {
            $this->validateSourceItemData($sourceItemData);

            $sourceId = $sourceItemData[SourceItemInterface::SOURCE_ID];
            if (isset($sourceItemsConfigsForDelete[$sourceId])) {
                $sourceItem = $sourceItemsConfigsForDelete[$sourceId];
            } else {
                /** @var SourceItemInterface $sourceItem */
                $sourceItem = $this->sourceItemConfigurationFactory->create();
            }

            if ($sourceItemData['notify_stock_qty_use_default'] == 1) {
                unset($sourceItemData['notify_stock_qty']);
            }

            $sourceItemData[SourceItemInterface::SKU] = $sku;
            $this->dataObjectHelper->populateWithArray(
                $sourceItem,
                $sourceItemData,
                SourceItemConfigurationInterface::class
            );

            $sourceItemsConfigsForSave[] = $sourceItem;
            unset($sourceItemsConfigsForDelete[$sourceId]);
        }
        if ($sourceItemsConfigsForSave) {
            $this->sourceItemConfigurationSave->execute($sourceItemsConfigsForSave);
        }
        if ($sourceItemsConfigsForDelete) {
            $this->deleteSourceItemsConfiguration($sourceItemsConfigsForDelete);
        }
    }

    /**
     * Key is source id, value is Source Item Configuration
     *
     * @param string $sku
     * @param array $sourceItemsData
     * @return array
     */
    private function getCurrentSourceItemsMap(string $sku, array $sourceItemsData): array
    {
        $sourceItemsConfigs = [];

        /** @var \Magento\Inventory\Model\SourceItem $sourceItem */
        foreach ($sourceItemsData as $sourceItem) {
            $sourceId = $sourceItem[SourceItemInterface::SOURCE_ID];
            $sourceItemConfig = $this->getSourceItemConfiguration->execute((int)$sourceId, $sku);

            if (null !== $sourceItemConfig) {
                $sourceItemsConfigs[] = $sourceItemConfig;
            }
        }

        $sourceItemsConfigsMap = [];
        if ($sourceItemsConfigs) {
            /** @var SourceItemConfigurationInterface $sourceItemConfig */
            foreach ($sourceItemsConfigs as $sourceItemConfig) {
                $sourceId = $sourceItemConfig->getSourceId();
                $sourceItemsConfigsMap[$sourceId] = $sourceItemConfig;
            }
        }

        return $sourceItemsConfigsMap;
    }

    /**
     * @param array $sourceItemData
     * @return void
     * @throws InputException
     */
    private function validateSourceItemData(array $sourceItemData)
    {
        if (!isset($sourceItemData[SourceItemInterface::SOURCE_ID])) {
            throw new InputException(__('Wrong Product to Source relation parameters given.'));
        }
    }

    /**
     * @param SourceItemInterface[] $sourceItemsConfigurations
     * @return void
     */
    private function deleteSourceItemsConfiguration(array $sourceItemsConfigurations)
    {
        /** @var SourceItemInterface $sourceItemConfiguration */
        foreach ($sourceItemsConfigurations as $sourceItemConfiguration) {
            $this->sourceItemConfigurationDelete->execute(
                $sourceItemConfiguration->getSourceId(),
                $sourceItemConfiguration->getSku()
            );
        }
    }
}
