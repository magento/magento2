<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Implementation of basic operations for Source Item entity for specific db layer
 */
class SourceItem extends AbstractDb
{
    /**#@+
     * Constants related to specific db layer
     */
    const TABLE_NAME_SOURCE_ITEM = 'inventory_source_item';
    const ID_FIELD_NAME = 'source_item_id';
    /**#@-*/

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(self::TABLE_NAME_SOURCE_ITEM, self::ID_FIELD_NAME);
    }
}
