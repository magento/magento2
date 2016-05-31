<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Model\ResourceModel\Bookmark;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Bookmark Collection
 */
class Collection extends AbstractCollection
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Ui\Model\Bookmark', 'Magento\Ui\Model\ResourceModel\Bookmark');
    }
}
