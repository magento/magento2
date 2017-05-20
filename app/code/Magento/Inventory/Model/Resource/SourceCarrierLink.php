<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Inventory\Model\Resource;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Inventory\Setup\InstallSchema;
use Magento\InventoryApi\Api\Data\SourceCarrierLinkInterface;

class SourceCarrierLink extends AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     *
     * @codingStandardsIgnore
     * @codeCoverageIgnore
     */
    protected function _construct()
    {
        $this->_init(InstallSchema::TABLE_NAME_SOURCE_CARRIER_LINK, 'source_carrier_link_id');
    }
}