<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Model\ResourceModel\Customer;

/**
 * Customers Report collection.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @api
 * @since 100.0.2
 */
class Collection extends \Magento\Customer\Model\ResourceModel\Customer\Collection
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
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory
     */
    protected $_quoteItemFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Collection
     */
    protected $orderResource;

    /**
     * Collection constructor.
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Eav\Model\EntityFactory $eavEntityFactory
     * @param \Magento\Eav\Model\ResourceModel\Helper $resourceHelper
     * @param \Magento\Framework\Validator\UniversalFactory $universalFactory
     * @param \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot $entitySnapshot
     * @param \Magento\Framework\DataObject\Copy\Config $fieldsetConfig
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory $quoteItemFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\Collection $orderResource
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null $connection
     * @param string $modelName
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Eav\Model\EntityFactory $eavEntityFactory,
        \Magento\Eav\Model\ResourceModel\Helper $resourceHelper,
        \Magento\Framework\Validator\UniversalFactory $universalFactory,
        \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot $entitySnapshot,
        \Magento\Framework\DataObject\Copy\Config $fieldsetConfig,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory $quoteItemFactory,
        \Magento\Sales\Model\ResourceModel\Order\Collection $orderResource,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
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
            $entitySnapshot,
            $fieldsetConfig,
            $connection,
            $modelName
        );
        $this->orderResource = $orderResource;
        $this->quoteRepository = $quoteRepository;
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
            try {
                $quote = $this->quoteRepository->getForCustomer($item->getId());

                $totals = $quote->getTotals();
                $item->setTotal($totals['subtotal']->getValue());
                $quoteItems = $this->_quoteItemFactory->create()->setQuoteFilter($quote->getId());
                $quoteItems->load();
                $item->setItems($quoteItems->count());
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
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
            $connection = $this->orderResource->getConnection();
            $baseSubtotalRefunded = $connection->getIfNullSql('orders.base_subtotal_refunded', 0);
            $baseSubtotalCanceled = $connection->getIfNullSql('orders.base_subtotal_canceled', 0);
            $baseDiscountCanceled = $connection->getIfNullSql('orders.base_discount_canceled', 0);

            $totalExpr = $this->_addOrderStatFilter ?
                "(orders.base_subtotal-{$baseSubtotalCanceled}-{$baseSubtotalRefunded} - {$baseDiscountCanceled}"
                    . " - ABS(orders.base_discount_amount))*orders.base_to_global_rate" :
                "orders.base_subtotal-{$baseSubtotalCanceled}-{$baseSubtotalRefunded} - {$baseDiscountCanceled}"
                    . " - ABS(orders.base_discount_amount)";

            $select = $this->orderResource->getConnection()->select();
            $select->from(
                ['orders' => $this->orderResource->getTable('sales_order')],
                [
                    'orders_avg_amount' => "AVG({$totalExpr})",
                    'orders_sum_amount' => "SUM({$totalExpr})",
                    'orders_count' => 'COUNT(orders.entity_id)',
                    'customer_id'
                ]
            )->where(
                'orders.state <> ?',
                \Magento\Sales\Model\Order::STATE_CANCELED
            )->where(
                'orders.customer_id IN(?)',
                $customerIds
            )->group(
                'orders.customer_id'
            );

            foreach ($this->orderResource->getConnection()->fetchAll($select) as $ordersInfo) {
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
        $countSelect->reset(\Magento\Framework\DB\Select::ORDER);
        $countSelect->reset(\Magento\Framework\DB\Select::LIMIT_COUNT);
        $countSelect->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET);
        $countSelect->reset(\Magento\Framework\DB\Select::COLUMNS);
        $countSelect->reset(\Magento\Framework\DB\Select::GROUP);
        $countSelect->reset(\Magento\Framework\DB\Select::HAVING);
        $countSelect->columns("count(DISTINCT e.entity_id)");

        return $countSelect;
    }
}
