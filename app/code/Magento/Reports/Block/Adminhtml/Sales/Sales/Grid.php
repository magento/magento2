<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Block\Adminhtml\Sales\Sales;

/**
 * Adminhtml sales report grid block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 * @since 2.0.0
 */
class Grid extends \Magento\Reports\Block\Adminhtml\Grid\AbstractGrid
{
    /**
     * GROUP BY criteria
     *
     * @var string
     * @since 2.0.0
     */
    protected $_columnGroupBy = 'period';

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setCountTotals(true);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getResourceCollectionName()
    {
        return $this->getFilterData()->getData('report_type') == 'updated_at_order'
            ? \Magento\Sales\Model\ResourceModel\Report\Order\Updatedat\Collection::class
            : \Magento\Sales\Model\ResourceModel\Report\Order\Collection::class;
    }

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @since 2.0.0
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
                'header_css_class' => 'col-orders',
                'column_css_class' => 'col-orders'
            ]
        );

        $this->addColumn(
            'total_qty_ordered',
            [
                'header' => __('Sales Items'),
                'index' => 'total_qty_ordered',
                'type' => 'number',
                'total' => 'sum',
                'sortable' => false,
                'header_css_class' => 'col-sales-items',
                'column_css_class' => 'col-sales-items'
            ]
        );

        $this->addColumn(
            'total_qty_invoiced',
            [
                'header' => __('Items'),
                'index' => 'total_qty_invoiced',
                'type' => 'number',
                'total' => 'sum',
                'sortable' => false,
                'visibility_filter' => ['show_actual_columns'],
                'header_css_class' => 'col-items',
                'column_css_class' => 'col-items'
            ]
        );

        if ($this->getFilterData()->getStoreIds()) {
            $this->setStoreIds(explode(',', $this->getFilterData()->getStoreIds()));
        }
        $currencyCode = $this->getCurrentCurrencyCode();
        $rate = $this->getRate($currencyCode);

        $this->addColumn(
            'total_income_amount',
            [
                'header' => __('Sales Total'),
                'type' => 'currency',
                'currency_code' => $currencyCode,
                'index' => 'total_income_amount',
                'total' => 'sum',
                'sortable' => false,
                'rate' => $rate,
                'header_css_class' => 'col-sales-total',
                'column_css_class' => 'col-sales-total'
            ]
        );

        $this->addColumn(
            'total_revenue_amount',
            [
                'header' => __('Revenue'),
                'type' => 'currency',
                'currency_code' => $currencyCode,
                'index' => 'total_revenue_amount',
                'total' => 'sum',
                'sortable' => false,
                'visibility_filter' => ['show_actual_columns'],
                'rate' => $rate,
                'header_css_class' => 'col-revenue',
                'column_css_class' => 'col-revenue'
            ]
        );

        $this->addColumn(
            'total_profit_amount',
            [
                'header' => __('Profit'),
                'type' => 'currency',
                'currency_code' => $currencyCode,
                'index' => 'total_profit_amount',
                'total' => 'sum',
                'sortable' => false,
                'visibility_filter' => ['show_actual_columns'],
                'rate' => $rate,
                'header_css_class' => 'col-profit',
                'column_css_class' => 'col-profit'
            ]
        );

        $this->addColumn(
            'total_invoiced_amount',
            [
                'header' => __('Invoiced'),
                'type' => 'currency',
                'currency_code' => $currencyCode,
                'index' => 'total_invoiced_amount',
                'total' => 'sum',
                'sortable' => false,
                'rate' => $rate,
                'header_css_class' => 'col-invoiced',
                'column_css_class' => 'col-invoiced'
            ]
        );

        $this->addColumn(
            'total_paid_amount',
            [
                'header' => __('Paid'),
                'type' => 'currency',
                'currency_code' => $currencyCode,
                'index' => 'total_paid_amount',
                'total' => 'sum',
                'sortable' => false,
                'visibility_filter' => ['show_actual_columns'],
                'rate' => $rate,
                'header_css_class' => 'col-paid',
                'column_css_class' => 'col-paid'
            ]
        );

        $this->addColumn(
            'total_refunded_amount',
            [
                'header' => __('Refunded'),
                'type' => 'currency',
                'currency_code' => $currencyCode,
                'index' => 'total_refunded_amount',
                'total' => 'sum',
                'sortable' => false,
                'rate' => $rate,
                'header_css_class' => 'col-refunded',
                'column_css_class' => 'col-refunded'
            ]
        );

        $this->addColumn(
            'total_tax_amount',
            [
                'header' => __('Sales Tax'),
                'type' => 'currency',
                'currency_code' => $currencyCode,
                'index' => 'total_tax_amount',
                'total' => 'sum',
                'sortable' => false,
                'rate' => $rate,
                'header_css_class' => 'col-sales-tax',
                'column_css_class' => 'col-sales-tax'
            ]
        );

        $this->addColumn(
            'total_tax_amount_actual',
            [
                'header' => __('Tax'),
                'type' => 'currency',
                'currency_code' => $currencyCode,
                'index' => 'total_tax_amount_actual',
                'total' => 'sum',
                'sortable' => false,
                'visibility_filter' => ['show_actual_columns'],
                'rate' => $rate,
                'header_css_class' => 'col-tax',
                'column_css_class' => 'col-tax'
            ]
        );

        $this->addColumn(
            'total_shipping_amount',
            [
                'header' => __('Sales Shipping'),
                'type' => 'currency',
                'currency_code' => $currencyCode,
                'index' => 'total_shipping_amount',
                'total' => 'sum',
                'sortable' => false,
                'rate' => $rate,
                'header_css_class' => 'col-sales-shipping',
                'column_css_class' => 'col-sales-shipping'
            ]
        );

        $this->addColumn(
            'total_shipping_amount_actual',
            [
                'header' => __('Shipping'),
                'type' => 'currency',
                'currency_code' => $currencyCode,
                'index' => 'total_shipping_amount_actual',
                'total' => 'sum',
                'sortable' => false,
                'visibility_filter' => ['show_actual_columns'],
                'rate' => $rate,
                'header_css_class' => 'col-shipping',
                'column_css_class' => 'col-shipping'
            ]
        );

        $this->addColumn(
            'total_discount_amount',
            [
                'header' => __('Sales Discount'),
                'type' => 'currency',
                'currency_code' => $currencyCode,
                'index' => 'total_discount_amount',
                'total' => 'sum',
                'sortable' => false,
                'rate' => $rate,
                'header_css_class' => 'col-sales-discount',
                'column_css_class' => 'col-sales-discount'
            ]
        );

        $this->addColumn(
            'total_discount_amount_actual',
            [
                'header' => __('Discount'),
                'type' => 'currency',
                'currency_code' => $currencyCode,
                'index' => 'total_discount_amount_actual',
                'total' => 'sum',
                'sortable' => false,
                'visibility_filter' => ['show_actual_columns'],
                'rate' => $rate,
                'header_css_class' => 'col-discount',
                'column_css_class' => 'col-discount'
            ]
        );

        $this->addColumn(
            'total_canceled_amount',
            [
                'header' => __('Canceled'),
                'type' => 'currency',
                'currency_code' => $currencyCode,
                'index' => 'total_canceled_amount',
                'total' => 'sum',
                'sortable' => false,
                'rate' => $rate,
                'header_css_class' => 'col-canceled',
                'column_css_class' => 'col-canceled'
            ]
        );

        $this->addExportType('*/*/exportSalesCsv', __('CSV'));
        $this->addExportType('*/*/exportSalesExcel', __('Excel XML'));

        return parent::_prepareColumns();
    }
}
