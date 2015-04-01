<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Model\Resource;

use Magento\Framework\Model\Resource\Db\AbstractDb;

/**
 * Bookmark resource
 */
class Bookmark extends AbstractDb
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('ui_bookmark', 'bookmark_id');
    }
}
