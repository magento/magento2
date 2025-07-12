<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\ResourceModel\Report\Order;

/**
 * Report order collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends \Magento\Sales\Model\ResourceModel\Report\Collection\AbstractCollection
{
    /**
     * Period format
     *
     * @var string
     */
    protected $_periodFormat;

    /**
     * Aggregated Data Table
     *
     * @var string
     */
    protected $_aggregationTable = 'sales_order_aggregated_created';

    /**
     * Selected columns
     *
     * @var array
     */
    protected $_selectedColumns = [];

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Sales\Model\ResourceModel\Report $resource
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Sales\Model\ResourceModel\Report $resource,
        ?\Magento\Framework\DB\Adapter\AdapterInterface $connection = null
    ) {
        $resource->init($this->_aggregationTable);
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $resource, $connection);
    }

    /**
     * Get selected columns
     *
     * @return array
     */
    protected function _getSelectedColumns()
    {
        $connection = $this->getConnection();
        if ('month' == $this->_period) {
            $this->_periodFormat = $connection->getDateFormatSql('period', '%Y-%m');
        } elseif ('year' == $this->_period) {
            $this->_periodFormat = $connection->getDateExtractSql(
                'period',
                \Magento\Framework\DB\Adapter\AdapterInterface::INTERVAL_YEAR
            );
        } else {
            $this->_periodFormat = $connection->getDateFormatSql('period', '%Y-%m-%d');
        }

        if (!$this->isTotals()) {
            $this->_selectedColumns = [
                'period' => $this->_periodFormat,
                'orders_count' => 'SUM(orders_count)',
                'total_qty_ordered' => 'SUM(total_qty_ordered)',
                'total_qty_invoiced' => 'SUM(total_qty_invoiced)',
                'total_income_amount' => 'SUM(total_income_amount)',
                'total_revenue_amount' => 'SUM(total_revenue_amount)',
                'total_profit_amount' => 'SUM(total_profit_amount)',
                'total_invoiced_amount' => 'SUM(total_invoiced_amount)',
                'total_canceled_amount' => 'SUM(total_canceled_amount)',
                'total_paid_amount' => 'SUM(total_paid_amount)',
                'total_refunded_amount' => 'SUM(total_refunded_amount)',
                'total_tax_amount' => 'SUM(total_tax_amount)',
                'total_tax_amount_actual' => 'SUM(total_tax_amount_actual)',
                'total_shipping_amount' => 'SUM(total_shipping_amount)',
                'total_shipping_amount_actual' => 'SUM(total_shipping_amount_actual)',
                'total_discount_amount' => 'SUM(total_discount_amount)',
                'total_discount_amount_actual' => 'SUM(total_discount_amount_actual)',
            ];
        }

        if ($this->isTotals()) {
            $this->_selectedColumns = $this->getAggregatedColumns();
        }

        return $this->_selectedColumns;
    }

    /**
     * Apply custom columns before load
     *
     * @return $this
     */
    protected function _beforeLoad()
    {
        $this->getSelect()->from($this->getResource()->getMainTable(), $this->_getSelectedColumns());
        if (!$this->isTotals()) {
            $this->getSelect()->group($this->_periodFormat);
        }
        return parent::_beforeLoad();
    }
}
