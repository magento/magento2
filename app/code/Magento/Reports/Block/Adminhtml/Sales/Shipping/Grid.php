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
namespace Magento\Reports\Block\Adminhtml\Sales\Shipping;

/**
 * Adminhtml shipping report grid block
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Grid extends \Magento\Reports\Block\Adminhtml\Grid\AbstractGrid
{
    /**
     * @var string
     */
    protected $_columnGroupBy = 'period';

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setCountTotals(true);
        $this->setCountSubTotals(true);
    }

    /**
     * @return string
     */
    public function getResourceCollectionName()
    {
        return $this->getFilterData()->getData(
            'report_type'
        ) ==
            'created_at_shipment' ? 'Magento\Sales\Model\Resource\Report\Shipping\Collection\Shipment' : 'Magento\Sales\Model\Resource\Report\Shipping\Collection\Order';
    }

    /**
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'period',
            array(
                'header' => __('Interval'),
                'index' => 'period',
                'sortable' => false,
                'period_type' => $this->getPeriodType(),
                'renderer' => 'Magento\Reports\Block\Adminhtml\Sales\Grid\Column\Renderer\Date',
                'totals_label' => __('Total'),
                'subtotals_label' => __('Subtotal'),
                'html_decorators' => array('nobr'),
                'header_css_class' => 'col-period',
                'column_css_class' => 'col-period'
            )
        );

        $this->addColumn(
            'shipping_description',
            array(
                'header' => __('Carrier/Method'),
                'index' => 'shipping_description',
                'sortable' => false,
                'header_css_class' => 'col-method',
                'column_css_class' => 'col-method'
            )
        );

        $this->addColumn(
            'orders_count',
            array(
                'header' => __('Orders'),
                'index' => 'orders_count',
                'total' => 'sum',
                'type' => 'number',
                'sortable' => false,
                'header_css_class' => 'col-qty',
                'column_css_class' => 'col-qty'
            )
        );

        if ($this->getFilterData()->getStoreIds()) {
            $this->setStoreIds(explode(',', $this->getFilterData()->getStoreIds()));
        }

        $currencyCode = $this->getCurrentCurrencyCode();
        $rate = $this->getRate($currencyCode);

        $this->addColumn(
            'total_shipping',
            array(
                'header' => __('Total Sales Shipping'),
                'type' => 'currency',
                'currency_code' => $currencyCode,
                'index' => 'total_shipping',
                'total' => 'sum',
                'sortable' => false,
                'rate' => $rate,
                'header_css_class' => 'col-total-sales-shipping',
                'column_css_class' => 'col-total-sales-shipping'
            )
        );

        $this->addColumn(
            'total_shipping_actual',
            array(
                'header' => __('Total Shipping'),
                'type' => 'currency',
                'currency_code' => $currencyCode,
                'index' => 'total_shipping_actual',
                'total' => 'sum',
                'sortable' => false,
                'rate' => $rate,
                'header_css_class' => 'col-total-shipping',
                'column_css_class' => 'col-total-shipping'
            )
        );

        $this->addExportType('*/*/exportShippingCsv', __('CSV'));
        $this->addExportType('*/*/exportShippingExcel', __('Excel XML'));

        return parent::_prepareColumns();
    }
}
