<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Implementation of basic operations for SourceCarrierLink entity for specific db layer
 * This class needed for internal purposes only, to make collection work properly
 */
class SourceCarrierLink extends AbstractDb
{
    /**#@+
     * Constants related to specific db layer
     */
    const TABLE_NAME_SOURCE_CARRIER_LINK = 'inventory_source_carrier_link';
    const ID_FIELD_NAME = 'link_id';
    /**#@-*/

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(self::TABLE_NAME_SOURCE_CARRIER_LINK, self::ID_FIELD_NAME);
    }
}
