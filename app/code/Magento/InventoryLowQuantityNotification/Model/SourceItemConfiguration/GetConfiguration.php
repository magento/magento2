<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryLowQuantityNotification\Model\SourceItemConfiguration;

use Magento\Framework\Api\DataObjectHelper;
use Magento\InventoryLowQuantityNotification\Model\ResourceModel\SourceItemConfiguration\GetData as GetDataModel;
use Magento\InventoryLowQuantityNotificationApi\Api\Data\SourceItemConfigurationInterface;
use Magento\InventoryLowQuantityNotificationApi\Api\Data\SourceItemConfigurationInterfaceFactory;

/**
 * Get Low Stock notification configuration from Product, if unset fallback to system values.
 */
class GetConfiguration
{
    /**
     * @var GetDataModel
     */
    private $getDataResourceModel;

    /**
     * @var GetDefaultValues
     */
    private $getDefaultValues;

    /**
     * @var SourceItemConfigurationInterfaceFactory
     */
    private $sourceItemConfigurationFactory;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @param GetDataModel $getDataResourceModel
     * @param GetDefaultValues $getDefaultValues
     * @param SourceItemConfigurationInterfaceFactory $sourceItemConfigurationFactory
     * @param DataObjectHelper $dataObjectHelper
     */
    public function __construct(
        GetDataModel $getDataResourceModel,
        GetDefaultValues $getDefaultValues,
        SourceItemConfigurationInterfaceFactory $sourceItemConfigurationFactory,
        DataObjectHelper $dataObjectHelper
    ) {
        $this->getDataResourceModel = $getDataResourceModel;
        $this->getDefaultValues = $getDefaultValues;
        $this->sourceItemConfigurationFactory = $sourceItemConfigurationFactory;
        $this->dataObjectHelper = $dataObjectHelper;
    }

    /**
     * Get Low Stock notification configuration from Product, if unset fallback to system values.
     *
     * @param string $sourceCode
     * @param string $sku
     * @return SourceItemConfigurationInterface
     */
    public function execute(string $sourceCode, string $sku): SourceItemConfigurationInterface
    {
        $sourceItemConfigurationData = $this->getDataResourceModel->execute($sourceCode, $sku);

        if (null === $sourceItemConfigurationData) {
            $sourceItemConfigurationData = $this->getDefaultValues->execute($sourceCode, $sku);
        }

        /** @var SourceItemConfigurationInterface $sourceItem */
        $sourceItemConfiguration = $this->sourceItemConfigurationFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $sourceItemConfiguration,
            $sourceItemConfigurationData,
            SourceItemConfigurationInterface::class
        );
        return $sourceItemConfiguration;
    }
}
