<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Model\ResourceModel\Product\Index;

/**
 * Reports Product Index Abstract Resource Model
 */
abstract class AbstractIndex extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Reports helper
     *
     * @var \Magento\Reports\Model\ResourceModel\Helper
     */
    protected $_resourceHelper;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Reports\Model\ResourceModel\Helper $resourceHelper
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Reports\Model\ResourceModel\Helper $resourceHelper,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->_resourceHelper = $resourceHelper;
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
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getMainTable())
            ->where('visitor_id = ?', $object->getVisitorId());

        $rowSet = $select->query()->fetchAll();
        foreach ($rowSet as $row) {
            /* We need to determine if there are rows with known
               customer for current product.
               */

            $select = $connection->select()->from(
                $this->getMainTable()
            )->where(
                'customer_id = ?',
                $object->getCustomerId()
            )->where(
                'product_id = ?',
                $row['product_id']
            );
            $idx = $connection->fetchRow($select);

            if ($idx) {
                /**
                 * If we are here it means that we have two rows: one with known customer and second with guest visitor
                 * One row should be updated with customer_id, second should be deleted
                 */
                $connection->delete($this->getMainTable(), ['index_id = ?' => $row['index_id']]);
                $where = ['index_id = ?' => $idx['index_id']];
                $data = [
                    'visitor_id' => $object->getVisitorId(),
                    'store_id' => $object->getStoreId(),
                ];
            } else {
                $where = ['index_id = ?' => $row['index_id']];
                $data = [
                    'customer_id' => $object->getCustomerId(),
                    'store_id' => $object->getStoreId(),
                ];
            }

            $connection->update($this->getMainTable(), $data, $where);
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
        $this->getConnection()->update($this->getMainTable(), $bind, $where);

        return $this;
    }

    /**
     * Save Product Index data (forced save)
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this|\Magento\Framework\Model\ResourceModel\Db\AbstractDb
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
            $select = $this->getConnection()->select()->from(
                ['main_table' => $this->getMainTable()],
                [$this->getIdFieldName()]
            )->joinLeft(
                ['visitor_table' => $this->getTable('customer_visitor')],
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
            $indexIds = $this->getConnection()->fetchCol($select);

            if (!$indexIds) {
                break;
            }

            $this->getConnection()->delete(
                $this->getMainTable(),
                $this->getConnection()->quoteInto($this->getIdFieldName() . ' IN(?)', $indexIds)
            );
        }
        return $this;
    }

    /**
     * Add information about product ids to visitor/customer
     *
     * @param \Magento\Framework\DataObject|\Magento\Reports\Model\Product\Index\AbstractIndex $object
     * @param int[] $productIds
     * @return $this
     */
    public function registerIds(\Magento\Framework\DataObject $object, $productIds)
    {
        $row = [
            'visitor_id' => $object->getVisitorId(),
            'customer_id' => $object->getCustomerId(),
            'store_id' => $object->getStoreId(),
        ];
        $data = [];
        foreach ($productIds as $productId) {
            $productId = (int)$productId;
            if ($productId) {
                $row['product_id'] = $productId;
                $data[] = $row;
            }
        }

        $matchFields = ['product_id', 'store_id'];
        foreach ($data as $row) {
            $this->_resourceHelper->mergeVisitorProductIndex($this->getMainTable(), $row, $matchFields);
        }
        return $this;
    }
}
