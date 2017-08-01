<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Model\ResourceModel\Bookmark;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Bookmark Collection
 * @since 2.0.0
 */
class Collection extends AbstractCollection
{
    /**
     * Resource initialization
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init(\Magento\Ui\Model\Bookmark::class, \Magento\Ui\Model\ResourceModel\Bookmark::class);
    }
}
