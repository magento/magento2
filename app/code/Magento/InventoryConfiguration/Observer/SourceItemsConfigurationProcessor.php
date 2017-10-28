<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfiguration\Observer;

use Magento\Framework\Exception\InputException;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryConfiguration\Api\Data\SourceItemConfigurationInterface;
use Magento\InventoryConfiguration\Model\SourceItemConfiguration\DeleteInterface;
use Magento\InventoryConfiguration\Model\SourceItemConfiguration\GetSourceItemConfiguration;
use Magento\InventoryConfiguration\Model\SourceItemConfiguration\SourceItemConfigurationSave;
use Magento\InventoryConfiguration\Model\SourceItemConfigurationFactory;

use Magento\Framework\Api\DataObjectHelper;

/**
 * Process source item configuration.
 */
class SourceItemsConfigurationProcessor
{
    /**
     * @var GetSourceItemConfiguration
     */
    private $getSourceItemConfiguration;

    /**
     * @var SourceItemConfigurationFactory
     */
    private $sourceItemConfigurationFactory;
    /**
     * @var SourceItemConfigurationSave
     */
    private $sourceItemConfigurationSave;
    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var DeleteInterface
     */
    private $sourceItemConfigurationDelete;

    /**
     * @param SourceItemConfigurationFactory $sourceItemConfigurationFactory
     * @param SourceItemConfigurationSave $sourceItemConfigurationSave
     * @param GetSourceItemConfiguration $getSourceItemConfiguration
     * @param DeleteInterface $sourceItemConfigurationDelete
     * @param DataObjectHelper $dataObjectHelper
     */
    public function __construct(
        SourceItemConfigurationFactory $sourceItemConfigurationFactory,
        SourceItemConfigurationSave $sourceItemConfigurationSave,
        GetSourceItemConfiguration $getSourceItemConfiguration,
        DeleteInterface $sourceItemConfigurationDelete,
        DataObjectHelper $dataObjectHelper
    ) {
        $this->getSourceItemConfiguration = $getSourceItemConfiguration;
        $this->sourceItemConfigurationSave = $sourceItemConfigurationSave;
        $this->sourceItemConfigurationFactory = $sourceItemConfigurationFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->sourceItemConfigurationDelete = $sourceItemConfigurationDelete;
    }

    /**
     * @param string $sku
     * @param array $sourceItemsData
     * @return void
     * @throws InputException
     */
    public function process($sku, array $sourceItemsData)
    {
        $sourceItemsForDelete = $this->getCurrentSourceItemsMap($sku, $sourceItemsData);
        $sourceItemsForSave = [];

        foreach ($sourceItemsData as $sourceItemData) {
            $this->validateSourceItemData($sourceItemData);

            $sourceId = $sourceItemData[SourceItemInterface::SOURCE_ID];
            if (isset($sourceItemsForDelete[$sourceId])) {
                $sourceItem = $sourceItemsForDelete[$sourceId];
            } else {
                /** @var SourceItemInterface $sourceItem */
                $sourceItem = $this->sourceItemConfigurationFactory->create();
            }

            $sourceItemData[SourceItemInterface::SKU] = $sku;
            $this->dataObjectHelper->populateWithArray($sourceItem, $sourceItemData, SourceItemConfigurationInterface::class);

            $sourceItemsForSave[] = $sourceItem;
            unset($sourceItemsForDelete[$sourceId]);
        }
        if ($sourceItemsForSave) {
            $this->sourceItemConfigurationSave->saveSourceItemConfiguration($sourceItemsForSave);
        }
        if ($sourceItemsForDelete) {
            $this->deleteSourceItemsConfiguration($sourceItemsForDelete);
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
        $sourceItems = [];

        /** @var \Magento\Inventory\Model\SourceItem $sourceItem */
        foreach($sourceItemsData as $sourceItem) {
            $sourceId = $sourceItem[SourceItemInterface::SOURCE_ID];
            $sourceItems[] = $this->getSourceItemConfiguration->getSourceItemConfiguration($sourceId, $sku);
        }

        $sourceItemMap = [];
        if ($sourceItems) {
            foreach ($sourceItems as $sourceItem) {
                $sourceItemMap[$sourceItem[SourceItemInterface::SOURCE_ID]] = $sourceItem;
            }
        }
        return $sourceItemMap;
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
        foreach ($sourceItemsConfigurations as $sourceItemConfiguration) {
            $this->sourceItemConfigurationDelete->delete($sourceItemConfiguration);
        }
    }
}
