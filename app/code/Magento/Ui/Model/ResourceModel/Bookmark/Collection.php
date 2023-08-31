<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Model\ResourceModel\Bookmark;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Ui\Model\Bookmark as ModelBookmark;
use Magento\Ui\Model\ResourceModel\Bookmark as ResourceBookmark;

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
        $this->_init(ModelBookmark::class, ResourceBookmark::class);
    }
}
