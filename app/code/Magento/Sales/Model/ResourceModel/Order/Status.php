<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\ResourceModel\Order;

use Magento\Framework\App\ResourceConnection;
use Psr\Log\LoggerInterface as LogWriter;
use Magento\Framework\Exception\LocalizedException;
use Magento\SalesSequence\Model\Manager;
use \Magento\Sales\Model\ResourceModel\EntityAbstract;
use \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot;

/**
 * Order status resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Status extends \Magento\Framework\Model\ResourceModel\Db\VersionControl\AbstractDb
{
    /**
     * Status labels table
     *
     * @var string
     */
    protected $labelsTable;

    /**
     * Status state table
     *
     * @var string
     */
    protected $stateTable;

    /**
     * Internal constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('sales_order_status', 'status');
        $this->_isPkAutoIncrement = false;
        $this->labelsTable = $this->getTable('sales_order_status_label');
        $this->stateTable = $this->getTable('sales_order_status_state');
    }

    /**
     * Retrieve select object for load object data
     *
     * @param string $field
     * @param mixed $value
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return \Magento\Framework\DB\Select
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        if ($field == 'default_state') {
            $select = $this->getConnection()->select()->from(
                $this->getMainTable(),
                ['label']
            )->join(
                ['state_table' => $this->stateTable],
                $this->getMainTable() . '.status = state_table.status',
                'status'
            )->where(
                'state_table.state = ?',
                $value
            )->order(
                'state_table.is_default DESC'
            )->limit(
                1
            );
        } else {
            $select = parent::_getLoadSelect($field, $value, $object);
        }
        return $select;
    }

    /**
     * Store labels getter
     *
     * @param \Magento\Sales\Model\Order\Status $status
     * @return array
     */
    public function getStoreLabels(\Magento\Sales\Model\Order\Status $status)
    {
        $select = $this->getConnection()->select()
            ->from(['ssl' => $this->labelsTable], [])
            ->where('status = ?', $status->getStatus())
            ->columns([
                'store_id',
                'label',
            ]);
        return $this->getConnection()->fetchPairs($select);
    }

    /**
     * Save status labels per store
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        if ($object->hasStoreLabels()) {
            $labels = $object->getStoreLabels();
            $this->getConnection()->delete($this->labelsTable, ['status = ?' => $object->getStatus()]);
            $data = [];
            foreach ($labels as $storeId => $label) {
                if (empty($label)) {
                    continue;
                }
                $data[] = ['status' => $object->getStatus(), 'store_id' => $storeId, 'label' => $label];
            }
            if (!empty($data)) {
                $this->getConnection()->insertMultiple($this->labelsTable, $data);
            }
        }
        return parent::_afterSave($object);
    }

    /**
     * Assign order status to order state
     *
     * @param string $status
     * @param string $state
     * @param bool $isDefault
     * @param bool $visibleOnFront
     * @return $this
     */
    public function assignState($status, $state, $isDefault, $visibleOnFront = false)
    {
        if ($isDefault) {
            $this->getConnection()->update(
                $this->stateTable,
                ['is_default' => 0],
                ['state = ?' => $state]
            );
        }
        $this->getConnection()->insertOnDuplicate(
            $this->stateTable,
            [
                'status' => $status,
                'state' => $state,
                'is_default' => (int)$isDefault,
                'visible_on_front' => (int)$visibleOnFront
            ]
        );
        return $this;
    }

    /**
     * Unassign order status from order state
     *
     * @param string $status
     * @param string $state
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function unassignState($status, $state)
    {
        $this->getConnection()->beginTransaction();
        try {
            $isStateDefault = $this->checkIsStateDefault($state, $status);
            $this->getConnection()->delete(
                $this->stateTable,
                [
                    'state = ?' => $state,
                    'status = ?' => $status
                ]
            );
            if ($isStateDefault) {
                $newDefaultStatus = $this->getStatusByState($state);
                if ($newDefaultStatus) {
                    $this->getConnection()->update(
                        $this->stateTable,
                        ['is_default' => 1],
                        [
                            'state = ?' => $state,
                            'status = ?' => $newDefaultStatus
                        ]
                    );
                }
            }
            $this->getConnection()->commit();
        } catch (\Exception $e) {
            $this->getConnection()->rollBack();
            throw new LocalizedException(__('Cannot unassign status from state'));
        }

        return $this;
    }

    /**
     * Check is this state last
     *
     * @param string $state
     * @return bool
     */
    public function checkIsStateLast($state)
    {
        return (1 == $this->getConnection()->fetchOne(
            $this->getConnection()->select()
                ->from(['sss' => $this->stateTable], [])
                ->where('state = ?', $state)
                ->columns([new\Zend_Db_Expr('COUNT(1)')])
        ));
    }

    /**
     * Check is this status used in orders
     *
     * @param string $status
     * @return bool
     */
    public function checkIsStatusUsed($status)
    {
        return (bool)$this->getConnection()->fetchOne(
            $this->getConnection()->select()
                ->from(['sfo' => $this->getTable('sales_order')], [])
                ->where('status = ?', $status)
                ->limit(1)
                ->columns([new \Zend_Db_Expr(1)])
        );
    }

    /**
     * Check is this pair of state and status default
     *
     * @param string $state
     * @param string $status
     * @return bool
     */
    protected function checkIsStateDefault($state, $status)
    {
        return (bool)$this->getConnection()->fetchOne(
            $this->getConnection()->select()
                ->from(['sss' => $this->stateTable], [])
                ->where('state = ?', $state)
                ->where('status = ?', $status)
                ->limit(1)
                ->columns(['is_default'])
        );
    }

    /**
     * Returns any possible status for state
     *
     * @param string $state
     * @return string
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function getStatusByState($state)
    {
        return (string)$this->getConnection()->fetchOne(
            $select = $this->getConnection()->select()
                ->from(['sss' => $this->stateTable, []])
                ->where('state = ?', $state)
                ->limit(1)
                ->columns(['status'])
        );
    }
}
