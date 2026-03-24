<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Catalog\Model;

use Magento\Framework\Model\AbstractModel;

class CompareList extends AbstractModel
{
    /**
     * Initialize resource
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\Product\Compare\CompareList::class);
    }
}
