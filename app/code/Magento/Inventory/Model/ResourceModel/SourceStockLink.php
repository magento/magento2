<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Inventory\Setup\InstallSchema;

/**
 * This class needed for service internal purposes only, to make collection work properly
 */
class SourceStockLink extends AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(InstallSchema::TABLE_NAME_SOURCE_STOCK_LINK, 'link_id');
    }
}
