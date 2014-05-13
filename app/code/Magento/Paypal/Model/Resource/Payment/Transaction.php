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
namespace Magento\Paypal\Model\Resource\Payment;

/**
 * Paypal transaction resource model
 *
 * @deprecated since 1.6.2.0
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Transaction extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    /**
     * Serializeable field: additional_information
     *
     * @var array
     */
    protected $_serializableFields = array('additional_information' => array(null, array()));

    /**
     * Initialize main table and the primary key field name
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('paypal_payment_transaction', 'transaction_id');
    }

    /**
     * Load the transaction object by specified txn_id
     *
     * @param \Magento\Paypal\Model\Payment\Transaction $transaction
     * @param string $txnId
     * @return void
     */
    public function loadObjectByTxnId(\Magento\Paypal\Model\Payment\Transaction $transaction, $txnId)
    {
        $select = $this->_getLoadByUniqueKeySelect($txnId);
        $data = $this->_getWriteAdapter()->fetchRow($select);
        $transaction->setData($data);
        $this->unserializeFields($transaction);
        $this->_afterLoad($transaction);
    }

    /**
     * Serialize additional information, if any
     *
     * @param \Magento\Framework\Model\AbstractModel $transaction
     * @return $this
     * @throws \Magento\Framework\Model\Exception
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $transaction)
    {
        $txnId = $transaction->getData('txn_id');
        $idFieldName = $this->getIdFieldName();

        // make sure unique key won't cause trouble
        if ($transaction->isFailsafe()) {
            $autoincrementId = (int)$this->_lookupByTxnId($txnId, $idFieldName);
            if ($autoincrementId) {
                $transaction->setData($idFieldName, $autoincrementId)->isObjectNew(false);
            }
        }

        return parent::_beforeSave($transaction);
    }

    /**
     * Load cell/row by specified unique key parts
     *
     * @param string $txnId
     * @param array|string|object $columns
     * @param bool $isRow
     * @return array|string
     */
    private function _lookupByTxnId($txnId, $columns, $isRow = false)
    {
        $select = $this->_getLoadByUniqueKeySelect($txnId, $columns);
        if ($isRow) {
            return $this->_getWriteAdapter()->fetchRow($select);
        }
        return $this->_getWriteAdapter()->fetchOne($select);
    }

    /**
     * Get select object for loading transaction by the unique key of order_id, payment_id, txn_id
     *
     * @param string $txnId
     * @param string|array|Zend_Db_Expr $columns
     * @return \Magento\Framework\DB\Select
     */
    private function _getLoadByUniqueKeySelect($txnId, $columns = '*')
    {
        return $this->_getWriteAdapter()->select()->from($this->getMainTable(), $columns)->where('txn_id = ?', $txnId);
    }
}
