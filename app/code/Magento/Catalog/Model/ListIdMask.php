<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * ListIdMask model
 */
class ListIdMask extends AbstractModel
{
    /**
     * Initialize resource
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\Product\Compare\ListIdMask::class);
    }
}
