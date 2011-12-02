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
 * @category    Mage
 * @package     Mage_Sales
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Credit memo API
 *
 * @category   Mage
 * @package    Mage_Sales
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Sales_Model_Order_Creditmemo_Api extends Mage_Sales_Model_Api_Resource
{

    /**
     * Initialize attributes' mapping
     */
    public function __construct()
    {
        $this->_attributesMap['creditmemo'] = array(
            'creditmemo_id' => 'entity_id'
        );
        $this->_attributesMap['creditmemo_item'] = array(
            'item_id'    => 'entity_id'
        );
        $this->_attributesMap['creditmemo_comment'] = array(
            'comment_id' => 'entity_id'
        );
    }

    /**
     * Retrieve credit memos by filters
     *
     * @param array|null $filter
     * @return array
     */
    public function items($filter = null)
    {
        $filter = $this->_prepareListFilter($filter);
        try {
            $result = array();
            /** @var $creditmemoModel Mage_Sales_Model_Order_Creditmemo */
            $creditmemoModel = Mage::getModel('Mage_Sales_Model_Order_Creditmemo');
            // map field name entity_id to creditmemo_id
            foreach ($creditmemoModel->getFilteredCollectionItems($filter) as $creditmemo) {
                $result[] = $this->_getAttributes($creditmemo, 'creditmemo');
            }
        } catch (Exception $e) {
            $this->_fault('invalid_filter', $e->getMessage());
        }
        return $result;
    }

    /**
     * Make filter of appropriate format for list method
     *
     * @param array|null $filter
     * @return array|null
     */
    protected function _prepareListFilter($filter = null)
    {
        // prepare filter, map field creditmemo_id to entity_id
        if (is_array($filter)) {
            foreach ($filter as $field => $value) {
                if (isset($this->_attributesMap['creditmemo'][$field])) {
                    $filter[$this->_attributesMap['creditmemo'][$field]] = $value;
                    unset($filter[$field]);
                }
            }
        }
        return $filter;
    }

    /**
     * Retrieve credit memo information
     *
     * @param string $creditmemoIncrementId
     * @return array
     */
    public function info($creditmemoIncrementId)
    {
        $creditmemo = $this->_getCreditmemo($creditmemoIncrementId);
        // get credit memo attributes with entity_id' => 'creditmemo_id' mapping
        $result = $this->_getAttributes($creditmemo, 'creditmemo');
        $result['order_increment_id'] = $creditmemo->getOrder()->load($creditmemo->getOrderId())->getIncrementId();
        // items refunded
        $result['items'] = array();
        foreach ($creditmemo->getAllItems() as $item) {
            $result['items'][] = $this->_getAttributes($item, 'creditmemo_item');
        }
        // credit memo comments
        $result['comments'] = array();
        foreach ($creditmemo->getCommentsCollection() as $comment) {
            $result['comments'][] = $this->_getAttributes($comment, 'creditmemo_comment');
        }
        return $result;
    }

    /**
     * Create new credit memo for order
     *
     * @param string $orderIncrementId
     * @param array $data array('qtys' => array('sku1' => qty1, ... , 'skuN' => qtyN),
     *      'shipping_amount' => value, 'adjustment_positive' => value, 'adjustment_negative' => value)
     * @param string|null $comment
     * @param bool $notifyCustomer
     * @param bool $includeComment
     * @param string $refundToStoreCreditAmount
     * @return string $creditmemoIncrementId
     */
    public function create($orderIncrementId, $data = null, $comment = null, $notifyCustomer = false,
        $includeComment = false, $refundToStoreCreditAmount = null)
    {
        /** @var $order Mage_Sales_Model_Order */
        $order = Mage::getModel('Mage_Sales_Model_Order')->load($orderIncrementId, 'increment_id');
        if (!$order->getId()) {
            $this->_fault('order_not_exists');
        }
        if (!$order->canCreditmemo()) {
            $this->_fault('cannot_create_creditmemo');
        }
        $data = $this->_prepareCreateData($data);

        /** @var $service Mage_Sales_Model_Service_Order */
        $service = Mage::getModel('Mage_Sales_Model_Service_Order', $order);
        /** @var $creditmemo Mage_Sales_Model_Order_Creditmemo */
        $creditmemo = $service->prepareCreditmemo($data);

        // refund to Store Credit
        if ($refundToStoreCreditAmount) {
            // check if refund to Store Credit is available
            if ($order->getCustomerIsGuest()) {
                $this->_fault('cannot_refund_to_storecredit');
            }
            $refundToStoreCreditAmount = max(
                0,
                min($creditmemo->getBaseCustomerBalanceReturnMax(), $refundToStoreCreditAmount)
            );
            if ($refundToStoreCreditAmount) {
                $refundToStoreCreditAmount = $creditmemo->getStore()->roundPrice($refundToStoreCreditAmount);
                $creditmemo->setBaseCustomerBalanceTotalRefunded($refundToStoreCreditAmount);
                $refundToStoreCreditAmount = $creditmemo->getStore()->roundPrice(
                    $refundToStoreCreditAmount*$order->getStoreToOrderRate()
                );
                // this field can be used by customer balance observer
                $creditmemo->setBsCustomerBalTotalRefunded($refundToStoreCreditAmount);
                // setting flag to make actual refund to customer balance after credit memo save
                $creditmemo->setCustomerBalanceRefundFlag(true);
            }
        }
        $creditmemo->setPaymentRefundDisallowed(true)->register();
        // add comment to creditmemo
        if (!empty($comment)) {
            $creditmemo->addComment($comment, $notifyCustomer);
        }
        try {
            Mage::getModel('Mage_Core_Model_Resource_Transaction')
                ->addObject($creditmemo)
                ->addObject($order)
                ->save();
            // send email notification
            $creditmemo->sendEmail($notifyCustomer, ($includeComment ? $comment : ''));
        } catch (Mage_Core_Exception $e) {
            $this->_fault('data_invalid', $e->getMessage());
        }
        return $creditmemo->getIncrementId();
    }

    /**
     * Add comment to credit memo
     *
     * @param string $creditmemoIncrementId
     * @param string $comment
     * @param boolean $notifyCustomer
     * @param boolean $includeComment
     * @return boolean
     */
    public function addComment($creditmemoIncrementId, $comment, $notifyCustomer = false, $includeComment = false)
    {
        $creditmemo = $this->_getCreditmemo($creditmemoIncrementId);
        try {
            $creditmemo->addComment($comment, $notifyCustomer)->save();
            $creditmemo->sendUpdateEmail($notifyCustomer, ($includeComment ? $comment : ''));
        } catch (Mage_Core_Exception $e) {
            $this->_fault('data_invalid', $e->getMessage());
        }

        return true;
    }

    /**
     * Cancel credit memo
     *
     * @param string $creditmemoIncrementId
     * @return boolean
     */
    public function cancel($creditmemoIncrementId)
    {
        $creditmemo = $this->_getCreditmemo($creditmemoIncrementId);

        if (!$creditmemo->canCancel()) {
            $this->_fault('status_not_changed', Mage::helper('Mage_Sales_Helper_Data')->__('Credit memo cannot be canceled.'));
        }
        try {
            $creditmemo->cancel()->save();
        } catch (Exception $e) {
            $this->_fault('status_not_changed', Mage::helper('Mage_Sales_Helper_Data')->__('Credit memo canceling problem.'));
        }

        return true;
    }

    /**
     * Hook method, could be replaced in derived classes
     *
     * @param  array $data
     * @return array
     */
    protected function _prepareCreateData($data)
    {
        $data = isset($data) ? $data : array();

        if (isset($data['qtys']) && count($data['qtys'])) {
            $qtysArray = array();
            foreach ($data['qtys'] as $qKey => $qVal) {
                // Save backward compatibility
                if (is_array($qVal)) {
                    if (isset($qVal['order_item_id']) && isset($qVal['qty'])) {
                        $qtysArray[$qVal['order_item_id']] = $qVal['qty'];
                    }
                } else {
                    $qtysArray[$qKey] = $qVal;
                }
            }
            $data['qtys'] = $qtysArray;
        }
        return $data;
    }

    /**
     * Load CreditMemo by IncrementId
     *
     * @param mixed $incrementId
     * @return Mage_Core_Model_Abstract|Mage_Sales_Model_Order_Creditmemo
     */
    protected function _getCreditmemo($incrementId)
    {
        /** @var $creditmemo Mage_Sales_Model_Order_Creditmemo */
        $creditmemo = Mage::getModel('Mage_Sales_Model_Order_Creditmemo')->load($incrementId, 'increment_id');
        if (!$creditmemo->getId()) {
            $this->_fault('not_exists');
        }
        return $creditmemo;
    }

}
