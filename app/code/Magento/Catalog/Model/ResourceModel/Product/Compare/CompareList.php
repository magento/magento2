<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Catalog\Model\ResourceModel\Product\Compare;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Compare List resource class
 */
class CompareList extends AbstractDb
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init('catalog_compare_list', 'list_id');
    }
}
