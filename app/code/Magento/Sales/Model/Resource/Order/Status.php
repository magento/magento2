<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Sales\Model\Resource\Order;

use Magento\Framework\Model\Exception;
use Magento\Framework\App\Resource;
use Magento\Framework\Logger as LogWriter;

/**
 * Order status resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Status extends \Magento\Framework\Model\Resource\Db\AbstractDb
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
     * @var \Magento\Framework\Logger
     */
    protected $logger;

    /**
     * Class constructor
     *
     * @param \Magento\Framework\App\Resource $resource
     * @param LogWriter $logger
     */
    public function __construct(
        Resource $resource,
        LogWriter $logger
    ) {
        $this->logger = $logger;
        parent::__construct($resource);
    }

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
     * @return \Zend_Db_Select
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        if ($field == 'default_state') {
            $select = $this->_getReadAdapter()->select()->from(
                $this->getMainTable(),
                array('label')
            )->join(
                array('state_table' => $this->stateTable),
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
        $select = $this->_getWriteAdapter()->select()
            ->from(['ssl' => $this->labelsTable], [])
            ->where('status = ?', $status->getStatus())
            ->columns([
                'store_id',
                'label'
            ]);
        return $this->_getReadAdapter()->fetchPairs($select);
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
            $this->_getWriteAdapter()->delete($this->labelsTable, array('status = ?' => $object->getStatus()));
            $data = array();
            foreach ($labels as $storeId => $label) {
                if (empty($label)) {
                    continue;
                }
                $data[] = array('status' => $object->getStatus(), 'store_id' => $storeId, 'label' => $label);
            }
            if (!empty($data)) {
                $this->_getWriteAdapter()->insertMultiple($this->labelsTable, $data);
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
            $this->_getWriteAdapter()->update(
                $this->stateTable,
                ['is_default' => 0],
                ['state = ?' => $state]
            );
        }
        $this->_getWriteAdapter()->insertOnDuplicate(
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
     * @throws \Magento\Framework\Model\Exception
     */
    public function unassignState($status, $state)
    {
        $this->_getWriteAdapter()->beginTransaction();
        try {
            $isStateDefault = $this->checkIsStateDefault($state, $status);
            $this->_getWriteAdapter()->delete(
                $this->stateTable,
                [
                    'state = ?' => $state,
                    'status = ?' => $status
                ]
            );
            if ($isStateDefault) {
                $newDefaultStatus = $this->getStatusByState($state);
                if ($newDefaultStatus) {
                    $this->_getWriteAdapter()->update(
                        $this->stateTable,
                        ['is_default' => 1],
                        [
                            'state = ?' => $state,
                            'status = ?' => $newDefaultStatus
                        ]
                    );
                }
            }
            $this->_getWriteAdapter()->commit();
        } catch (\Exception $e) {
            $this->_getWriteAdapter()->rollBack();
            throw new Exception('Cannot unassing status from state');
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
        return (1 == $this->_getWriteAdapter()->fetchOne(
            $this->_getWriteAdapter()->select()
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
        return (bool)$this->_getWriteAdapter()->fetchOne(
            $this->_getWriteAdapter()->select()
                ->from(['sfo' => $this->getTable('sales_flat_order')], [])
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
        return (bool)$this->_getWriteAdapter()->fetchOne(
            $this->_getWriteAdapter()->select()
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
     */
    protected function getStatusByState($state)
    {
        return (string)$this->_getWriteAdapter()->fetchOne(
            $select = $this->_getWriteAdapter()->select()
                ->from(['sss' => $this->stateTable, []])
                ->where('state = ?', $state)
                ->limit(1)
                ->columns(['status'])
        );
    }
}
