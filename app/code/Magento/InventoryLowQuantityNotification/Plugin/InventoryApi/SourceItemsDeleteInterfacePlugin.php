<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryLowQuantityNotification\Plugin\InventoryApi;

use Magento\InventoryApi\Api\SourceItemsDeleteInterface;
use Magento\InventoryLowQuantityNotificationApi\Api\DeleteSourceItemsConfigurationInterface;

/**
 * This plugin keeps consistency between SourceItem and SourceItemConfiguration while deleting
 */
class SourceItemsDeleteInterfacePlugin
{
    /**
     * @var DeleteSourceItemsConfigurationInterface
     */
    private $deleteSourceItemsConfiguration;

    /**
     * SourceItemsDeleteInterfacePlugin constructor.
     * @param DeleteSourceItemsConfigurationInterface $deleteSourceItemsConfiguration
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        DeleteSourceItemsConfigurationInterface $deleteSourceItemsConfiguration
    ) {
        $this->deleteSourceItemsConfiguration = $deleteSourceItemsConfiguration;
    }

    /**
     * Keep database consistency while a source item is removed
     *
     * @param SourceItemsDeleteInterface $subject
     * @param void $result
     * @param array $sourceItems
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(
        SourceItemsDeleteInterface $subject,
        $result,
        array $sourceItems
    ) {
        $this->deleteSourceItemsConfiguration->execute($sourceItems);
    }
}
