<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * @method string getHashedId()
 * @method CompareList setHashedId()
 * @method int getCustomerId()
 * @method CompareList setCustomerId()
 */
class CompareList extends AbstractModel
{
    /**
     * Initialize resource
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\Catalog\Model\ResourceModel\CompareList::class);
    }
}
