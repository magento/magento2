<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Model\Resource;

/**
 * Downloadable Product  Samples resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Sample extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    /**
     * Initialize connection
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('downloadable_sample', 'sample_id');
    }

    /**
     * Save title of sample item in store scope
     *
     * @param \Magento\Downloadable\Model\Sample $sampleObject
     * @return $this
     */
    public function saveItemTitle($sampleObject)
    {
        $adapter = $this->getConnection();
        $sampleTitleTable = $this->getTable('downloadable_sample_title');
        $bind = [':sample_id' => $sampleObject->getId(), ':store_id' => (int)$sampleObject->getStoreId()];
        $select = $adapter->select()->from(
            $sampleTitleTable
        )->where(
            'sample_id=:sample_id AND store_id=:store_id'
        );
        if ($adapter->fetchOne($select, $bind)) {
            $where = [
                'sample_id = ?' => $sampleObject->getId(),
                'store_id = ?' => (int)$sampleObject->getStoreId(),
            ];
            if ($sampleObject->getUseDefaultTitle()) {
                $adapter->delete($sampleTitleTable, $where);
            } else {
                $adapter->update($sampleTitleTable, ['title' => $sampleObject->getTitle()], $where);
            }
        } else {
            if (!$sampleObject->getUseDefaultTitle()) {
                $adapter->insert(
                    $sampleTitleTable,
                    [
                        'sample_id' => $sampleObject->getId(),
                        'store_id' => (int)$sampleObject->getStoreId(),
                        'title' => $sampleObject->getTitle()
                    ]
                );
            }
        }
        return $this;
    }

    /**
     * Delete data by item(s)
     *
     * @param \Magento\Downloadable\Model\Sample|array|int $items
     * @return $this
     */
    public function deleteItems($items)
    {
        $adapter = $this->getConnection();
        $where = '';
        if ($items instanceof \Magento\Downloadable\Model\Sample) {
            $where = ['sample_id = ?' => $items->getId()];
        } else {
            $where = ['sample_id in (?)' => $items];
        }
        if ($where) {
            $adapter->delete($this->getMainTable(), $where);
            $adapter->delete($this->getTable('downloadable_sample_title'), $where);
        }
        return $this;
    }

    /**
     * Retrieve links searchable data
     *
     * @param int $productId
     * @param int $storeId
     * @return array
     */
    public function getSearchableData($productId, $storeId)
    {
        $adapter = $this->getConnection();
        $ifNullDefaultTitle = $adapter->getIfNullSql('st.title', 'd.title');
        $select = $adapter->select()->from(
            ['m' => $this->getMainTable()],
            null
        )->join(
            ['d' => $this->getTable('downloadable_sample_title')],
            'd.sample_id=m.sample_id AND d.store_id=0',
            []
        )->joinLeft(
            ['st' => $this->getTable('downloadable_sample_title')],
            'st.sample_id=m.sample_id AND st.store_id=:store_id',
            ['title' => $ifNullDefaultTitle]
        )->where(
            'm.product_id=:product_id',
            $productId
        );
        $bind = [':store_id' => (int)$storeId, ':product_id' => $productId];

        return $adapter->fetchCol($select, $bind);
    }
}
