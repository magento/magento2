<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfiguration\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\InventoryConfigurationApi\Api\Data\SourceItemConfigurationInterface;

// TODO: remove this class
/**
 * Implementation of basic operations for Source Item Notification entity for specific db layer
 */
class SourceItemConfiguration
{
    /**
     * Constants related to specific db layer
     */
    const TABLE_NAME_SOURCE_ITEM_CONFIGURATION = 'inventory_source_item_configuration';
}