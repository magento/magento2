<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Resource\Report\Order;

/**
 * Report order collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends \Magento\Sales\Model\Resource\Report\Collection\AbstractCollection
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
     * @param \Magento\Core\Model\EntityFactory $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Sales\Model\Resource\Report $resource
     * @param \Zend_Db_Adapter_Abstract $connection
     */
    public function __construct(
        \Magento\Core\Model\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Sales\Model\Resource\Report $resource,
        $connection = null
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
        $adapter = $this->getConnection();
        if ('month' == $this->_period) {
            $this->_periodFormat = $adapter->getDateFormatSql('period', '%Y-%m');
        } elseif ('year' == $this->_period) {
            $this->_periodFormat = $adapter->getDateExtractSql(
                'period',
                \Magento\Framework\DB\Adapter\AdapterInterface::INTERVAL_YEAR
            );
        } else {
            $this->_periodFormat = $adapter->getDateFormatSql('period', '%Y-%m-%d');
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
     * Add selected data
     *
     * @return $this
     */
    protected function _initSelect()
    {
        $this->getSelect()->from($this->getResource()->getMainTable(), $this->_getSelectedColumns());
        if (!$this->isTotals()) {
            $this->getSelect()->group($this->_periodFormat);
        }
        return parent::_initSelect();
    }
}
