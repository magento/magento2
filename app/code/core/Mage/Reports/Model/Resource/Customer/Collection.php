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
 * @package     Mage_Reports
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Customers Report collection
 *
 * @category    Mage
 * @package     Mage_Reports
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Reports_Model_Resource_Customer_Collection extends Mage_Customer_Model_Resource_Customer_Collection
{
    /**
     * Add order statistics flag
     *
     * @var boolean
     */
    protected $_addOrderStatistics           = false;

    /**
     * Add order statistics is filter flag
     *
     * @var boolean
     */
    protected $_addOrderStatisticsIsFilter   = false;

    /**
     * Customer id table name
     *
     * @var string
     */
    protected $_customerIdTableName;

    /**
     * Customer id field name
     *
     * @var string
     */
    protected $_customerIdFieldName;

    /**
     * Order entity table name
     *
     * @var string
     */
    protected $_orderEntityTableName;

    /**
     * Order entity field name
     *
     * @var string
     */
    protected $_orderEntityFieldName;

    /**
     * Add cart info to collection
     *
     * @return Mage_Reports_Model_Resource_Customer_Collection
     */
    public function addCartInfo()
    {
        foreach ($this->getItems() as $item) {
            $quote = Mage::getModel('Mage_Sales_Model_Quote')->loadByCustomer($item->getId());

            if ($quote instanceof Mage_Sales_Model_Quote) {
                $totals = $quote->getTotals();
                $item->setTotal($totals['subtotal']->getValue());
                $quoteItems = Mage::getResourceModel('Mage_Sales_Model_Resource_Quote_Item_Collection')
                    ->setQuoteFilter($quote->getId());
                $quoteItems->load();
                $item->setItems($quoteItems->count());
            } else {
                $item->remove();
            }

        }
        return $this;
    }

    /**
     * Add customer name to results
     *
     * @return Mage_Reports_Model_Resource_Customer_Collection
     */
    public function addCustomerName()
    {
        $this->addNameToSelect();
        return $this;
    }

    /**
     * Order for each customer
     *
     * @param string $from
     * @param string $to
     * @return Mage_Reports_Model_Resource_Customer_Collection
     */
    public function joinOrders($from = '', $to = '')
    {
        if ($from != '' && $to != '') {
            $dateFilter = " AND orders.created_at BETWEEN '{$from}' AND '{$to}'";
        } else {
            $dateFilter = '';
        }

        $this->getSelect()
            ->joinLeft(array('orders' => $this->getTable('sales_flat_order')),
                "orders.customer_id = e.entity_id".$dateFilter,
            array());

        return $this;
    }

    /**
     * Add orders count
     *
     * @return Mage_Reports_Model_Resource_Customer_Collection
     */
    public function addOrdersCount()
    {
        $this->getSelect()
            ->columns(array("orders_count" => "COUNT(orders.entity_id)"))
            ->where('orders.state <> ?', Mage_Sales_Model_Order::STATE_CANCELED)
            ->group("e.entity_id");

        return $this;
    }

    /**
     * Order summary info for each customer
     * such as orders_count, orders_avg_amount, orders_total_amount
     *
     * @param int $storeId
     * @return Mage_Reports_Model_Resource_Customer_Collection
     */
    public function addSumAvgTotals($storeId = 0)
    {
        $adapter = $this->getConnection();
        $baseSubtotalRefunded   = $adapter->getIfNullSql('orders.base_subtotal_refunded', 0);
        $baseSubtotalCanceled   = $adapter->getIfNullSql('orders.base_subtotal_canceled', 0);

        /**
         * calculate average and total amount
         */
        $expr = ($storeId == 0)
            ? "(orders.base_subtotal - {$baseSubtotalCanceled} - {$baseSubtotalRefunded}) * orders.base_to_global_rate"
            : "orders.base_subtotal - {$baseSubtotalCanceled} - {$baseSubtotalRefunded}";

        $this->getSelect()
            ->columns(array("orders_avg_amount" => "AVG({$expr})"))
            ->columns(array("orders_sum_amount" => "SUM({$expr})"));

        return $this;
    }

    /**
     * Order by total amount
     *
     * @param string $dir
     * @return Mage_Reports_Model_Resource_Customer_Collection
     */
    public function orderByTotalAmount($dir = self::SORT_ORDER_DESC)
    {
        $this->getSelect()
            ->order("orders_sum_amount {$dir}");
        return $this;
    }

    /**
     * Add order statistics
     *
     * @param boolean $isFilter
     * @return Mage_Reports_Model_Resource_Customer_Collection
     */
    public function addOrdersStatistics($isFilter = false)
    {
        $this->_addOrderStatistics          = true;
        $this->_addOrderStatisticsIsFilter  = (bool)$isFilter;
        return $this;
    }

    /**
     * Add orders statistics to collection items
     *
     * @return Mage_Reports_Model_Resource_Customer_Collection
     */
    protected function _addOrdersStatistics()
    {
        $customerIds = $this->getColumnValues($this->getResource()->getIdFieldName());

        if ($this->_addOrderStatistics && !empty($customerIds)) {
            $adapter = $this->getConnection();
            $baseSubtotalRefunded   = $adapter->getIfNullSql('orders.base_subtotal_refunded', 0);
            $baseSubtotalCanceled   = $adapter->getIfNullSql('orders.base_subtotal_canceled', 0);

            $totalExpr = ($this->_addOrderStatisticsIsFilter)
                ? "(orders.base_subtotal-{$baseSubtotalCanceled}-{$baseSubtotalRefunded})*orders.base_to_global_rate"
                : "orders.base_subtotal-{$baseSubtotalCanceled}-{$baseSubtotalRefunded}";

            $select = $this->getConnection()->select();
            $select->from(array('orders'=>$this->getTable('sales_flat_order')), array(
                'orders_avg_amount' => "AVG({$totalExpr})",
                'orders_sum_amount' => "SUM({$totalExpr})",
                'orders_count' => 'COUNT(orders.entity_id)',
                'customer_id'
            ))->where('orders.state <> ?', Mage_Sales_Model_Order::STATE_CANCELED)
              ->where('orders.customer_id IN(?)', $customerIds)
              ->group('orders.customer_id');

            /*
             * Analytic functions usage
             */
            $select = Mage::getResourceHelper('Mage_Core')->getQueryUsingAnalyticFunction($select);

            foreach ($this->getConnection()->fetchAll($select) as $ordersInfo) {
                $this->getItemById($ordersInfo['customer_id'])->addData($ordersInfo);
            }
        }

        return $this;
    }

    /**
     * Collection after load operations like adding orders statistics
     *
     * @return Mage_Reports_Model_Resource_Customer_Collection
     */
    protected function _afterLoad()
    {
        $this->_addOrdersStatistics();
        return $this;
    }

    /**
     * Order by customer registration
     *
     * @param string $dir
     * @return Mage_Reports_Model_Resource_Customer_Collection
     */
    public function orderByCustomerRegistration($dir = self::SORT_ORDER_DESC)
    {
        $this->addAttributeToSort('entity_id', $dir);
        return $this;
    }

    /**
     * Get select count sql
     *
     * @return string
     */
    public function getSelectCountSql()
    {
        $countSelect = clone $this->getSelect();
        $countSelect->reset(Zend_Db_Select::ORDER);
        $countSelect->reset(Zend_Db_Select::LIMIT_COUNT);
        $countSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
        $countSelect->reset(Zend_Db_Select::COLUMNS);
        $countSelect->reset(Zend_Db_Select::GROUP);
        $countSelect->reset(Zend_Db_Select::HAVING);
        $countSelect->columns("count(DISTINCT e.entity_id)");

        return $countSelect;
    }
}
