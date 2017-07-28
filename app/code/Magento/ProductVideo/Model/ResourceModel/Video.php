<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ProductVideo\Model\ResourceModel;

/**
 * Class \Magento\ProductVideo\Model\ResourceModel\Video
 *
 * @since 2.0.0
 */
class Video extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Resource initialization
     *
     * @return void
     * @since 2.0.0
     */
    public function _construct()
    {
        $this->_init(\Magento\ProductVideo\Setup\InstallSchema::GALLERY_VALUE_VIDEO_TABLE, 'value_id');
    }

    /**
     * @param array $data
     * @param array $fields
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
     */
    public function insertOnDuplicate(array $data, array $fields = [])
    {
        return $this->getConnection()->insertOnDuplicate($this->getMainTable(), $data, $fields);
    }

    /**
     * @param array $ids
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
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
