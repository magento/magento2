<?php
/**
 * URL rewrite resource model
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\UrlRewrite\Model\ResourceModel;

/**
 * Class \Magento\UrlRewrite\Model\ResourceModel\UrlRewrite
 *
 */
class UrlRewrite extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
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
     * @param \Magento\UrlRewrite\Model\UrlRewrite $object
     * @return \Magento\Framework\DB\Select
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        /** @var $select \Magento\Framework\DB\Select */
        $select = parent::_getLoadSelect($field, $value, $object);

        if ($object->getStoreId() !== null) {
            $select->where(
                'store_id IN(?)',
                [\Magento\Store\Model\Store::DEFAULT_STORE_ID, $object->getStoreId()]
            );
            $select->order('store_id ' . \Magento\Framework\DB\Select::SQL_DESC);
            $select->limit(1);
        }

        return $select;
    }
}
