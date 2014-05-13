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


/**
 * Customers Report collection
 */
namespace Magento\Reports\Model\Resource\Customer;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Collection extends \Magento\Customer\Model\Resource\Customer\Collection
{
    /**
     * Add order statistics flag
     *
     * @var bool
     */
    protected $_addOrderStatistics = false;

    /**
     * Add order statistics is filter flag
     *
     * @var bool
     */
    protected $_addOrderStatFilter = false;

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
    protected $_orderEntityTable;

    /**
     * Order entity field name
     *
     * @var string
     */
    protected $_orderEntityField;

    /**
     * @var \Magento\Sales\Model\QuoteFactory
     */
    protected $_quoteFactory;

    /**
     * @var \Magento\Sales\Model\Resource\Quote\Item\CollectionFactory
     */
    protected $_quoteItemFactory;

    /**
     * @param \Magento\Core\Model\EntityFactory $entityFactory
     * @param \Magento\Framework\Logger $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Eav\Model\EntityFactory $eavEntityFactory
     * @param \Magento\Eav\Model\Resource\Helper $resourceHelper
     * @param \Magento\Framework\Validator\UniversalFactory $universalFactory
     * @param \Magento\Framework\Object\Copy\Config $fieldsetConfig
     * @param \Magento\Sales\Model\QuoteFactory $quoteFactory
     * @param \Magento\Sales\Model\Resource\Quote\Item\CollectionFactory $quoteItemFactory
     * @param mixed $connection
     * @param string $modelName
     * 
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Core\Model\EntityFactory $entityFactory,
        \Magento\Framework\Logger $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\App\Resource $resource,
        \Magento\Eav\Model\EntityFactory $eavEntityFactory,
        \Magento\Eav\Model\Resource\Helper $resourceHelper,
        \Magento\Framework\Validator\UniversalFactory $universalFactory,
        \Magento\Framework\Object\Copy\Config $fieldsetConfig,
        \Magento\Sales\Model\QuoteFactory $quoteFactory,
        \Magento\Sales\Model\Resource\Quote\Item\CollectionFactory $quoteItemFactory,
        $connection = null,
        $modelName = self::CUSTOMER_MODEL_NAME
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $eavConfig,
            $resource,
            $eavEntityFactory,
            $resourceHelper,
            $universalFactory,
            $fieldsetConfig,
            $connection,
            $modelName
        );
        $this->_quoteFactory = $quoteFactory;
        $this->_quoteItemFactory = $quoteItemFactory;
    }

    /**
     * Add cart info to collection
     *
     * @return $this
     */
    public function addCartInfo()
    {
        foreach ($this->getItems() as $item) {
            $quote = $this->_quoteFactory->create()->loadByCustomer($item->getId());

            if ($quote instanceof \Magento\Sales\Model\Quote) {
                $totals = $quote->getTotals();
                $item->setTotal($totals['subtotal']->getValue());
                $quoteItems = $this->_quoteItemFactory->create()->setQuoteFilter($quote->getId());
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
     * @return $this
     */
    public function addCustomerName()
    {
        $this->addNameToSelect();
        return $this;
    }

    /**
     * Order for each customer
     *
     * @param string $fromDate
     * @param string $toDate
     * @return $this
     */
    public function joinOrders($fromDate = '', $toDate = '')
    {
        if ($fromDate != '' && $toDate != '') {
            $dateFilter = " AND orders.created_at BETWEEN '{$fromDate}' AND '{$toDate}'";
        } else {
            $dateFilter = '';
        }

        $this->getSelect()->joinLeft(
            array('orders' => $this->getTable('sales_flat_order')),
            "orders.customer_id = e.entity_id" . $dateFilter,
            array()
        );

        return $this;
    }

    /**
     * Add orders count
     *
     * @return $this
     */
    public function addOrdersCount()
    {
        $this->getSelect()->columns(
            array("orders_count" => "COUNT(orders.entity_id)")
        )->where(
            'orders.state <> ?',
            \Magento\Sales\Model\Order::STATE_CANCELED
        )->group(
            "e.entity_id"
        );

        return $this;
    }

    /**
     * Order summary info for each customer such as orders_count, orders_avg_amount, orders_total_amount
     *
     * @param int $storeId
     * @return $this
     */
    public function addSumAvgTotals($storeId = 0)
    {
        $adapter = $this->getConnection();
        $baseSubtotalRefunded = $adapter->getIfNullSql('orders.base_subtotal_refunded', 0);
        $baseSubtotalCanceled = $adapter->getIfNullSql('orders.base_subtotal_canceled', 0);

        /**
         * calculate average and total amount
         */
        $expr = $storeId ==
            0 ?
            "(orders.base_subtotal - {$baseSubtotalCanceled} - {$baseSubtotalRefunded}) * orders.base_to_global_rate" :
            "orders.base_subtotal - {$baseSubtotalCanceled} - {$baseSubtotalRefunded}";

        $this->getSelect()->columns(
            array("orders_avg_amount" => "AVG({$expr})")
        )->columns(
            array("orders_sum_amount" => "SUM({$expr})")
        );

        return $this;
    }

    /**
     * Order by total amount
     *
     * @param string $dir
     * @return $this
     */
    public function orderByTotalAmount($dir = self::SORT_ORDER_DESC)
    {
        $this->getSelect()->order("orders_sum_amount {$dir}");
        return $this;
    }

    /**
     * Add order statistics
     *
     * @param bool $isFilter
     * @return $this
     */
    public function addOrdersStatistics($isFilter = false)
    {
        $this->_addOrderStatistics = true;
        $this->_addOrderStatFilter = (bool)$isFilter;
        return $this;
    }

    /**
     * Add orders statistics to collection items
     *
     * @return $this
     */
    protected function _addOrdersStatistics()
    {
        $customerIds = $this->getColumnValues($this->getResource()->getIdFieldName());

        if ($this->_addOrderStatistics && !empty($customerIds)) {
            $adapter = $this->getConnection();
            $baseSubtotalRefunded = $adapter->getIfNullSql('orders.base_subtotal_refunded', 0);
            $baseSubtotalCanceled = $adapter->getIfNullSql('orders.base_subtotal_canceled', 0);

            $totalExpr = $this->_addOrderStatFilter ?
                "(orders.base_subtotal-{$baseSubtotalCanceled}-{$baseSubtotalRefunded})*orders.base_to_global_rate" :
                "orders.base_subtotal-{$baseSubtotalCanceled}-{$baseSubtotalRefunded}";

            $select = $this->getConnection()->select();
            $select->from(
                array('orders' => $this->getTable('sales_flat_order')),
                array(
                    'orders_avg_amount' => "AVG({$totalExpr})",
                    'orders_sum_amount' => "SUM({$totalExpr})",
                    'orders_count' => 'COUNT(orders.entity_id)',
                    'customer_id'
                )
            )->where(
                'orders.state <> ?',
                \Magento\Sales\Model\Order::STATE_CANCELED
            )->where(
                'orders.customer_id IN(?)',
                $customerIds
            )->group(
                'orders.customer_id'
            );

            foreach ($this->getConnection()->fetchAll($select) as $ordersInfo) {
                $this->getItemById($ordersInfo['customer_id'])->addData($ordersInfo);
            }
        }

        return $this;
    }

    /**
     * Collection after load operations like adding orders statistics
     *
     * @return $this
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
     * @return $this
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
        $countSelect->reset(\Zend_Db_Select::ORDER);
        $countSelect->reset(\Zend_Db_Select::LIMIT_COUNT);
        $countSelect->reset(\Zend_Db_Select::LIMIT_OFFSET);
        $countSelect->reset(\Zend_Db_Select::COLUMNS);
        $countSelect->reset(\Zend_Db_Select::GROUP);
        $countSelect->reset(\Zend_Db_Select::HAVING);
        $countSelect->columns("count(DISTINCT e.entity_id)");

        return $countSelect;
    }
}
