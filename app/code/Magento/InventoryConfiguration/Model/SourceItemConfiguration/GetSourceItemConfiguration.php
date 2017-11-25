<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfiguration\Model\SourceItemConfiguration;

use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\InputException;
use Magento\InventoryConfiguration\Model\ResourceModel\SourceItemConfiguration\GetSourceItemConfiguration as ResourceGetSourceItemConfiguration;
use Magento\InventoryConfigurationApi\Api\Data\SourceItemConfigurationInterface;
use Magento\InventoryConfigurationApi\Api\Data\SourceItemConfigurationInterfaceFactory;
use Magento\InventoryConfigurationApi\Api\GetSourceItemConfigurationInterface;
use Psr\Log\LoggerInterface;

/**
 * Class GetSourceItemConfiguration
 */
class GetSourceItemConfiguration implements GetSourceItemConfigurationInterface
{
    /**
     * Default Notify Stock Qty config path
     */
    const XML_PATH_NOTIFY_STOCK_QTY = 'inventory/inventory_configuration/notify_stock_qty_default';

    /**
     * @var ResourceGetSourceItemConfiguration
     */
    private $getConfiguration;

    /**
     * @var SourceItemConfigurationInterfaceFactory
     */
    private $sourceItemConfigurationFactory;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * ResourceGetSourceItemConfiguration constructor.
     *
     * @param ResourceGetSourceItemConfiguration $getConfiguration
     * @param SourceItemConfigurationInterfaceFactory $sourceItemConfigurationFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param ScopeConfigInterface $scopeConfig
     * @param LoggerInterface $logger
     */
    public function __construct(
        ResourceGetSourceItemConfiguration $getConfiguration,
        SourceItemConfigurationInterfaceFactory $sourceItemConfigurationFactory,
        DataObjectHelper $dataObjectHelper,
        ScopeConfigInterface $scopeConfig,
        LoggerInterface $logger
    ) {
        $this->getConfiguration = $getConfiguration;
        $this->sourceItemConfigurationFactory = $sourceItemConfigurationFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function get(int $sourceId, string $sku): SourceItemConfigurationInterface
    {
        if (empty($sourceId) || empty($sku)) {
            throw new InputException(__('SourceId oder Sku missing'));
        }

        $sourceItemConfigurationData = $this->getConfiguration->execute($sourceId, $sku);

        if ($sourceItemConfigurationData === null) {
            $sourceItemConfigurationData = $this->getDefaultValues($sourceId, $sku);
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

    /**
     * Get default configuration in case of non-existent specific configuration for a source item
     *
     * @param int $sourceId
     * @param string $sku
     * @return array
     */
    private function getDefaultValues(int $sourceId, string $sku) : array
    {
        $inventoryNotifyQty = (float)$this->scopeConfig->getValue(self::XML_PATH_NOTIFY_STOCK_QTY);

        $defaultConfiguration = [
            SourceItemConfigurationInterface::SOURCE_ID => $sourceId,
            SourceItemConfigurationInterface::SKU => $sku,
            SourceItemConfigurationInterface::INVENTORY_NOTIFY_QTY => $inventoryNotifyQty,
        ];

        return $defaultConfiguration;
    }
}
