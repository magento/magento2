<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\ProductVideo\Model\ResourceModel;

use Magento\Framework\Exception\LocalizedException;

class Video extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Resource initialization
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('catalog_product_entity_media_gallery_value_video', 'value_id');
    }

    /**
     * Insert video data and update on duplicate
     *
     * @param array $data
     * @param array $fields
     * @return int
     * @throws LocalizedException
     */
    public function insertOnDuplicate(array $data, array $fields = [])
    {
        return $this->getConnection()->insertOnDuplicate($this->getMainTable(), $data, $fields);
    }

    /**
     * Load video data by ids
     *
     * @param array $ids
     * @return array
     * @throws LocalizedException
     */
    public function loadByIds(array $ids)
    {
        $select = $this->getConnection()->select()->from(
            $this->getMainTable()
        )->where(
            'value_id IN(?)',
            $ids,
            \Zend_Db::INT_TYPE
        );

        return $this->getConnection()->fetchAll($select);
    }

    /**
     * Load video data by ids and store id
     *
     * @param array $ids
     * @param int $storeId
     * @return array
     * @throws LocalizedException
     */
    public function loadByIdsAndStoreId(array $ids, int $storeId): array
    {
        $select = $this->getConnection()->select()->from(
            $this->getMainTable()
        )->where(
            'value_id IN(?)',
            $ids,
            \Zend_Db::INT_TYPE
        )->where(
            'store_id = ?',
            $storeId,
            \Zend_Db::INT_TYPE
        );

        return $this->getConnection()->fetchAll($select);
    }
}
