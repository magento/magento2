<?php

namespace Alexx\Blog\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * BlogPosts ResourceModel
 */
class BlogPosts extends AbstractDb
{
    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init(\Alexx\Blog\Model\BlogPosts::TBL_NAME, \Alexx\Blog\Model\BlogPosts::TBL_ENTITY);
    }
}
