<?php
/**
 * URL rewrite resource model
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\UrlRewrite\Model\ResourceModel;

use Magento\Framework\DB\Select;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Store\Model\Store;
use Magento\UrlRewrite\Model\UrlRewrite as ModelUrlRewrite;

class UrlRewrite extends AbstractDb
{
    /**
     * Define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('url_rewrite', 'url_rewrite_id');
    }

    /**
     * Initialize array fields
     *
     * @return $this
     */
    protected function _initUniqueFields()
    {
        $this->_uniqueFields = [
            ['field' => ['request_path', 'store_id'], 'title' => __('Request Path for Specified Store')],
        ];
        return $this;
    }

    /**
     * Retrieve select object for load object data
     *
     * @param string $field
     * @param mixed $value
     * @param ModelUrlRewrite $object
     * @return Select
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        /** @var Select $select */
        $select = parent::_getLoadSelect($field, $value, $object);

        if ($object->getStoreId() !== null) {
            $select->where(
                'store_id IN(?)',
                [Store::DEFAULT_STORE_ID, $object->getStoreId()]
            );
            $select->order('store_id ' . Select::SQL_DESC);
            $select->limit(1);
        }

        return $select;
    }
}
