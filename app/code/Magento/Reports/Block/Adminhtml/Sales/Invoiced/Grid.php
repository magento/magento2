<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Block\Adminhtml\Sales\Invoiced;

/**
 * Adminhtml invoiced report grid block
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Grid extends \Magento\Reports\Block\Adminhtml\Grid\AbstractGrid
{
    /**
     * GROUP BY condition
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
        return ($this->getFilterData()->getData('report_type')) == 'created_at_invoice'
            ? \Magento\Sales\Model\ResourceModel\Report\Invoiced\Collection\Invoiced::class
            : \Magento\Sales\Model\ResourceModel\Report\Invoiced\Collection\Order::class;
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
                'header' => __('Orders'),
                'index' => 'orders_count',
                'type' => 'number',
                'total' => 'sum',
                'sortable' => false,
                'header_css_class' => 'col-qty',
                'column_css_class' => 'col-qty'
            ]
        );

        $this->addColumn(
            'orders_invoiced',
            [
                'header' => __('Invoiced Orders'),
                'index' => 'orders_invoiced',
                'type' => 'number',
                'total' => 'sum',
                'sortable' => false,
                'header_css_class' => 'col-invoiced',
                'column_css_class' => 'col-invoiced'
            ]
        );

        $this->setStoreIds($this->_getStoreIds());
        $currencyCode = $this->getCurrentCurrencyCode();
        $rate = $this->getRate($currencyCode);

        $this->addColumn(
            'invoiced',
            [
                'header' => __('Total Invoiced'),
                'type' => 'currency',
                'currency_code' => $currencyCode,
                'index' => 'invoiced',
                'total' => 'sum',
                'sortable' => false,
                'rate' => $rate,
                'header_css_class' => 'col-total-invoiced',
                'column_css_class' => 'col-total-invoiced'
            ]
        );

        $this->addColumn(
            'invoiced_captured',
            [
                'header' => __('Paid Invoices'),
                'type' => 'currency',
                'currency_code' => $currencyCode,
                'index' => 'invoiced_captured',
                'total' => 'sum',
                'sortable' => false,
                'rate' => $rate,
                'header_css_class' => 'col-total-invoiced-paid',
                'column_css_class' => 'col-total-invoiced-paid'
            ]
        );

        $this->addColumn(
            'invoiced_not_captured',
            [
                'header' => __('Unpaid Invoices'),
                'type' => 'currency',
                'currency_code' => $currencyCode,
                'index' => 'invoiced_not_captured',
                'total' => 'sum',
                'sortable' => false,
                'rate' => $rate,
                'header_css_class' => 'col-total-invoiced-not-paid',
                'column_css_class' => 'col-total-invoiced-not-paid'
            ]
        );

        $this->addExportType('*/*/exportInvoicedCsv', __('CSV'));
        $this->addExportType('*/*/exportInvoicedExcel', __('Excel XML'));

        return parent::_prepareColumns();
    }
}
