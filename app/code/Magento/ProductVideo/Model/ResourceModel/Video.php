<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ProductVideo\Model\ResourceModel;

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
     * @param array $data
     * @param array $fields
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function insertOnDuplicate(array $data, array $fields = [])
    {
        return $this->getConnection()->insertOnDuplicate($this->getMainTable(), $data, $fields);
    }

    /**
     * @param array $ids
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function loadByIds(array $ids)
    {
        $select = $this->getConnection()->select()->from(
            $this->getMainTable()
        )->where(
            'value_id IN(?)',
            $ids
        );

        return $this->getConnection()->fetchAll($select);
    }
}
