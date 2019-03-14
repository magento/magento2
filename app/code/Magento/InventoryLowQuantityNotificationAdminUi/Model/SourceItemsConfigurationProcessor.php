<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryLowQuantityNotificationAdminUi\Model;

use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\InputException;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryLowQuantityNotificationApi\Api\Data\SourceItemConfigurationInterfaceFactory;
use Magento\InventoryLowQuantityNotificationApi\Api\Data\SourceItemConfigurationInterface;
use Magento\InventoryLowQuantityNotificationApi\Api\DeleteSourceItemsConfigurationInterface;
use Magento\InventoryLowQuantityNotificationApi\Api\SourceItemConfigurationsSaveInterface;

/**
 * Process source item configuration.
 */
class SourceItemsConfigurationProcessor
{
    /**
     * @var SourceItemConfigurationInterfaceFactory
     */
    private $sourceItemConfigurationFactory;

    /**
     * @var SourceItemConfigurationsSaveInterface
     */
    private $sourceItemConfigurationsSave;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var DeleteSourceItemsConfigurationInterface
     */
    private $sourceItemsConfigurationDelete;

    /**
     * SourceItemsConfigurationProcessor constructor.
     *
     * @param SourceItemConfigurationInterfaceFactory $sourceItemConfigurationFactory
     * @param SourceItemConfigurationsSaveInterface $sourceItemConfigurationsSave
     * @param DeleteSourceItemsConfigurationInterface $sourceItemsConfigurationDelete
     * @param DataObjectHelper $dataObjectHelper
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        SourceItemConfigurationInterfaceFactory $sourceItemConfigurationFactory,
        SourceItemConfigurationsSaveInterface $sourceItemConfigurationsSave,
        DeleteSourceItemsConfigurationInterface $sourceItemsConfigurationDelete,
        DataObjectHelper $dataObjectHelper
    ) {
        $this->sourceItemConfigurationsSave = $sourceItemConfigurationsSave;
        $this->sourceItemConfigurationFactory = $sourceItemConfigurationFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->sourceItemsConfigurationDelete = $sourceItemsConfigurationDelete;
    }

    /**
     * Process configuration
     *
     * @param string $sku
     * @param array $sourceItemsData
     * @return void
     * @throws InputException
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function process($sku, array $sourceItemsData)
    {
        $sourceItemConfigurations = [];
        foreach ($sourceItemsData as $sourceItemData) {
            $this->validateSourceItemData($sourceItemData);
            $sourceItemConfiguration = $this->sourceItemConfigurationFactory->create();

            if ($sourceItemData['notify_stock_qty_use_default'] == 1) {
                unset($sourceItemData['notify_stock_qty']);
            }

            $sourceItemData[SourceItemInterface::SKU] = $sku;
            $this->dataObjectHelper->populateWithArray(
                $sourceItemConfiguration,
                $sourceItemData,
                SourceItemConfigurationInterface::class
            );

            $sourceItemConfigurations[] = $sourceItemConfiguration;
        }

        if (count($sourceItemConfigurations) > 0) {
            $this->deleteSourceItemsConfiguration($sourceItemConfigurations);
            $this->sourceItemConfigurationsSave->execute($sourceItemConfigurations);
        }
    }

    /**
     * Perform validation on source item data
     *
     * @param array $sourceItemData
     * @return void
     * @throws InputException
     */
    private function validateSourceItemData(array $sourceItemData)
    {
        if (!isset($sourceItemData[SourceItemInterface::SOURCE_CODE])) {
            throw new InputException(__('Wrong Product to Source relation parameters given.'));
        }
    }

    /**
     * Delete source items configurations
     *
     * @param SourceItemInterface[] $sourceItemsConfigurations
     * @return void
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    private function deleteSourceItemsConfiguration(array $sourceItemsConfigurations)
    {
        /** @var SourceItemInterface $sourceItemConfiguration */
        $this->sourceItemsConfigurationDelete->execute($sourceItemsConfigurations);
    }
}
