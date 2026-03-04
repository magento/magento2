<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */

namespace Magento\Reports\Block\Adminhtml\Sales\Refunded;

use Magento\Reports\Block\Adminhtml\Grid\Column\Renderer\Currency;

/**
 * Adminhtml refunded report grid block
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Grid extends \Magento\Reports\Block\Adminhtml\Grid\AbstractGrid
{
    /**
     * GROUP BY criteria
     *
     * @var string
     */
    protected $_columnGroupBy = 'period';

    /**
     * @inheritdoc
     * @codeCoverageIgnore
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setCountTotals(true);
    }

    /**
     * @inheritdoc
     */
    public function getResourceCollectionName()
    {
        return $this->getFilterData()->getData('report_type') == 'created_at_refunded'
            ? \Magento\Sales\Model\ResourceModel\Report\Refunded\Collection\Refunded::class
            : \Magento\Sales\Model\ResourceModel\Report\Refunded\Collection\Order::class;
    }

    /**
     * @inheritdoc
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'period',
            [
                'header' => __('Interval'),
                'index' => 'period',
                'sortable' => false,
                'period_type' => $this->getPeriodType(),
                'renderer' => \Magento\Reports\Block\Adminhtml\Sales\Grid\Column\Renderer\Date::class,
                'totals_label' => __('Total'),
                'html_decorators' => ['nobr'],
                'header_css_class' => 'col-period',
                'column_css_class' => 'col-period'
            ]
        );

        $this->addColumn(
            'orders_count',
            [
                'header' => __('Refunded Orders'),
                'index' => 'orders_count',
                'type' => 'number',
                'total' => 'sum',
                'sortable' => false,
                'header_css_class' => 'col-qty',
                'column_css_class' => 'col-qty'
            ]
        );

        $this->setStoreIds($this->_getStoreIds());
        $currencyCode = $this->getCurrentCurrencyCode();
        $rate = $this->getRate($currencyCode);

        $this->addColumn(
            'refunded',
            [
                'header' => __('Total Refunded'),
                'type' => 'currency',
                'currency_code' => $currencyCode,
                'index' => 'refunded',
                'total' => 'sum',
                'sortable' => false,
                'rate' => $rate,
                'header_css_class' => 'col-ref-total',
                'column_css_class' => 'col-ref-total',
                'renderer' => Currency::class
            ]
        );

        $this->addColumn(
            'online_refunded',
            [
                'header' => __('Online Refunds'),
                'type' => 'currency',
                'currency_code' => $currencyCode,
                'index' => 'online_refunded',
                'total' => 'sum',
                'sortable' => false,
                'rate' => $rate,
                'header_css_class' => 'col-ref-online',
                'column_css_class' => 'col-ref-online',
                'renderer' => Currency::class
            ]
        );

        $this->addColumn(
            'offline_refunded',
            [
                'header' => __('Offline Refunds'),
                'type' => 'currency',
                'currency_code' => $currencyCode,
                'index' => 'offline_refunded',
                'total' => 'sum',
                'sortable' => false,
                'rate' => $rate,
                'header_css_class' => 'col-ref-offline',
                'column_css_class' => 'col-ref-offline',
                'renderer' => Currency::class
            ]
        );

        $this->addExportType('*/*/exportRefundedCsv', __('CSV'));
        $this->addExportType('*/*/exportRefundedExcel', __('Excel XML'));

        return parent::_prepareColumns();
    }
}
