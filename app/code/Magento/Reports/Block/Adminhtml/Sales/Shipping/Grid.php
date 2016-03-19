<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Block\Adminhtml\Sales\Shipping;

/**
 * Adminhtml shipping report grid block
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Grid extends \Magento\Reports\Block\Adminhtml\Grid\AbstractGrid
{
    /**
     * Group by criteria
     *
     * @var string
     */
    protected $_columnGroupBy = 'period';

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setCountTotals(true);
        $this->setCountSubTotals(true);
    }

    /**
     * {@inheritdoc}
     */
    public function getResourceCollectionName()
    {
        return $this->getFilterData()->getData('report_type') == 'created_at_shipment'
            ? 'Magento\Sales\Model\ResourceModel\Report\Shipping\Collection\Shipment'
            : 'Magento\Sales\Model\ResourceModel\Report\Shipping\Collection\Order';
    }

    /**
     * {@inheritdoc}
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
                'renderer' => 'Magento\Reports\Block\Adminhtml\Sales\Grid\Column\Renderer\Date',
                'totals_label' => __('Total'),
                'subtotals_label' => __('Subtotal'),
                'html_decorators' => ['nobr'],
                'header_css_class' => 'col-period',
                'column_css_class' => 'col-period'
            ]
        );

        $this->addColumn(
            'shipping_description',
            [
                'header' => __('Carrier/Method'),
                'index' => 'shipping_description',
                'sortable' => false,
                'header_css_class' => 'col-method',
                'column_css_class' => 'col-method'
            ]
        );

        $this->addColumn(
            'orders_count',
            [
                'header' => __('Orders'),
                'index' => 'orders_count',
                'total' => 'sum',
                'type' => 'number',
                'sortable' => false,
                'header_css_class' => 'col-qty',
                'column_css_class' => 'col-qty'
            ]
        );

        if ($this->getFilterData()->getStoreIds()) {
            $this->setStoreIds(explode(',', $this->getFilterData()->getStoreIds()));
        }

        $currencyCode = $this->getCurrentCurrencyCode();
        $rate = $this->getRate($currencyCode);

        $this->addColumn(
            'total_shipping',
            [
                'header' => __('Total Sales Shipping'),
                'type' => 'currency',
                'currency_code' => $currencyCode,
                'index' => 'total_shipping',
                'total' => 'sum',
                'sortable' => false,
                'rate' => $rate,
                'header_css_class' => 'col-total-sales-shipping',
                'column_css_class' => 'col-total-sales-shipping'
            ]
        );

        $this->addColumn(
            'total_shipping_actual',
            [
                'header' => __('Total Shipping'),
                'type' => 'currency',
                'currency_code' => $currencyCode,
                'index' => 'total_shipping_actual',
                'total' => 'sum',
                'sortable' => false,
                'rate' => $rate,
                'header_css_class' => 'col-total-shipping',
                'column_css_class' => 'col-total-shipping'
            ]
        );

        $this->addExportType('*/*/exportShippingCsv', __('CSV'));
        $this->addExportType('*/*/exportShippingExcel', __('Excel XML'));

        return parent::_prepareColumns();
    }
}
