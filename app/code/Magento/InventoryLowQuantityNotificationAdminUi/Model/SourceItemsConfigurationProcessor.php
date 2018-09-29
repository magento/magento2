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
use Magento\InventoryLowQuantityNotificationApi\Api\DeleteSourceItemConfigurationInterface;
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
     * @var DeleteSourceItemConfigurationInterface
     */
    private $sourceItemConfigurationDelete;

    /**
     * @param SourceItemConfigurationInterfaceFactory $sourceItemConfigurationFactory
     * @param SourceItemConfigurationsSaveInterface $sourceItemConfigurationsSave
     * @param DeleteSourceItemConfigurationInterface $sourceItemConfigurationDelete
     * @param DataObjectHelper $dataObjectHelper
     */
    public function __construct(
        SourceItemConfigurationInterfaceFactory $sourceItemConfigurationFactory,
        SourceItemConfigurationsSaveInterface $sourceItemConfigurationsSave,
        DeleteSourceItemConfigurationInterface $sourceItemConfigurationDelete,
        DataObjectHelper $dataObjectHelper
    ) {
        $this->sourceItemConfigurationsSave = $sourceItemConfigurationsSave;
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
     * @param SourceItemInterface[] $sourceItemsConfigurations
     * @return void
     */
    private function deleteSourceItemsConfiguration(array $sourceItemsConfigurations)
    {
        /** @var SourceItemInterface $sourceItemConfiguration */
        foreach ($sourceItemsConfigurations as $sourceItemConfiguration) {
            $this->sourceItemConfigurationDelete->execute(
                $sourceItemConfiguration->getSourceCode(),
                $sourceItemConfiguration->getSku()
            );
        }
    }
}
