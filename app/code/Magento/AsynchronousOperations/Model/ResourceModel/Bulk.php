<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
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
