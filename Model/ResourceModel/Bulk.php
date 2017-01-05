<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AsynchronousOperations\Model\ResourceModel;

/**
 * Class Bulk
 */
class Bulk extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize banner sales rule resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('magento_bulk', 'uuid');
    }
}
