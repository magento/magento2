<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Model\Resource\Product\Index;

/**
 * Reports Product Index Abstract Resource Model
 */
abstract class AbstractIndex extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @var \Magento\Reports\Model\Resource\Helper
     */
    protected $_resourceHelper;

    /**
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Reports\Model\Resource\Helper $resourceHelper
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     */
    public function __construct(
        \Magento\Framework\App\Resource $resource,
        \Magento\Reports\Model\Resource\Helper $resourceHelper,
        \Magento\Framework\Stdlib\DateTime $dateTime
    ) {
        parent::__construct($resource);
        $this->_resourceHelper = $resourceHelper;
        $this->dateTime = $dateTime;
    }

    /**
     * Update Customer from visitor (Customer logged in)
     *
     * @param \Magento\Reports\Model\Product\Index\AbstractIndex $object
     * @return $this
     */
    public function updateCustomerFromVisitor(\Magento\Reports\Model\Product\Index\AbstractIndex $object)
    {
        /**
         * Do nothing if customer not logged in
         */
        if (!$object->getCustomerId() || !$object->getVisitorId()) {
            return $this;
        }
        $adapter = $this->_getWriteAdapter();
        $select = $adapter->select()->from($this->getMainTable())->where('visitor_id = ?', $object->getVisitorId());

        $rowSet = $select->query()->fetchAll();
        foreach ($rowSet as $row) {
            /* We need to determine if there are rows with known
               customer for current product.
               */

            $select = $adapter->select()->from(
                $this->getMainTable()
            )->where(
                'customer_id = ?',
                $object->getCustomerId()
            )->where(
                'product_id = ?',
                $row['product_id']
            );
            $idx = $adapter->fetchRow($select);

            if ($idx) {
                /**
                 * If we are here it means that we have two rows: one with known customer, but second just visitor is set
                 * One row should be updated with customer_id, second should be deleted
                 */
                $adapter->delete($this->getMainTable(), ['index_id = ?' => $row['index_id']]);
                $where = ['index_id = ?' => $idx['index_id']];
                $data = [
                    'visitor_id' => $object->getVisitorId(),
                    'store_id' => $object->getStoreId(),
                    'added_at' => $this->dateTime->now(),
                ];
            } else {
                $where = ['index_id = ?' => $row['index_id']];
                $data = [
                    'customer_id' => $object->getCustomerId(),
                    'store_id' => $object->getStoreId(),
                    'added_at' => $this->dateTime->now(),
                ];
            }

            $adapter->update($this->getMainTable(), $data, $where);
        }
        return $this;
    }

    /**
     * Purge visitor data by customer (logout)
     *
     * @param \Magento\Reports\Model\Product\Index\AbstractIndex $object
     * @return $this
     */
    public function purgeVisitorByCustomer(\Magento\Reports\Model\Product\Index\AbstractIndex $object)
    {
        /**
         * Do nothing if customer not logged in
         */
        if (!$object->getCustomerId()) {
            return $this;
        }

        $bind = ['visitor_id' => null];
        $where = ['customer_id = ?' => (int)$object->getCustomerId()];
        $this->_getWriteAdapter()->update($this->getMainTable(), $bind, $where);

        return $this;
    }

    /**
     * Save Product Index data (forced save)
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this|\Magento\Framework\Model\Resource\Db\AbstractDb
     */
    public function save(\Magento\Framework\Model\AbstractModel $object)
    {
        if ($object->isDeleted()) {
            return $this->delete($object);
        }

        $this->_serializeFields($object);
        $this->_beforeSave($object);
        $this->_checkUnique($object);

        $data = $this->_prepareDataForSave($object);
        unset($data[$this->getIdFieldName()]);

        $matchFields = ['product_id', 'store_id'];

        $this->_resourceHelper->mergeVisitorProductIndex($this->getMainTable(), $data, $matchFields);

        $this->unserializeFields($object);
        $this->_afterSave($object);

        return $this;
    }

    /**
     * Clean index (visitor)
     *
     * @return $this
     */
    public function clean()
    {
        while (true) {
            $select = $this->_getReadAdapter()->select()->from(
                ['main_table' => $this->getMainTable()],
                [$this->getIdFieldName()]
            )->joinLeft(
                ['visitor_table' => $this->getTable('log_visitor')],
                'main_table.visitor_id = visitor_table.visitor_id',
                []
            )->where(
                'main_table.visitor_id > ?',
                0
            )->where(
                'visitor_table.visitor_id IS NULL'
            )->limit(
                100
            );
            $indexIds = $this->_getReadAdapter()->fetchCol($select);

            if (!$indexIds) {
                break;
            }

            $this->_getWriteAdapter()->delete(
                $this->getMainTable(),
                $this->_getWriteAdapter()->quoteInto($this->getIdFieldName() . ' IN(?)', $indexIds)
            );
        }
        return $this;
    }

    /**
     * Add information about product ids to visitor/customer
     *
     * @param \Magento\Framework\Object|\Magento\Reports\Model\Product\Index\AbstractIndex $object
     * @param array $productIds
     * @return $this
     */
    public function registerIds(\Magento\Framework\Object $object, $productIds)
    {
        $row = [
            'visitor_id' => $object->getVisitorId(),
            'customer_id' => $object->getCustomerId(),
            'store_id' => $object->getStoreId(),
        ];
        $addedAt = $this->dateTime->toTimestamp(true);
        $data = [];
        foreach ($productIds as $productId) {
            $productId = (int)$productId;
            if ($productId) {
                $row['product_id'] = $productId;
                $row['added_at'] = $this->dateTime->formatDate($addedAt);
                $data[] = $row;
            }
            $addedAt -= $addedAt > 0 ? 1 : 0;
        }

        $matchFields = ['product_id', 'store_id'];
        foreach ($data as $row) {
            $this->_resourceHelper->mergeVisitorProductIndex($this->getMainTable(), $row, $matchFields);
        }
        return $this;
    }
}
