<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\ResourceModel\Product\Compare;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * ListIdMask Resource model
 */
class ListIdMask extends AbstractDb
{
    /**
     * Main table and field initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('list_id_mask', 'entity_id');
    }
}
