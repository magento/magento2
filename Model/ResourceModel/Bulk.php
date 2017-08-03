<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AsynchronousOperations\Model\ResourceModel;

/**
 * Class Bulk
 * @since 2.2.0
 */
class Bulk extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize banner sales rule resource model
     *
     * @return void
     * @since 2.2.0
     */
    protected function _construct()
    {
        $this->_init('magento_bulk', 'uuid');
    }
}
