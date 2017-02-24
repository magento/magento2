<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Block\Adminhtml\Sales\Bestsellers;

/**
 * Adminhtml bestsellers report grid block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
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
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setCountTotals(true);
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getResourceCollectionName()
    {
        return \Magento\Sales\Model\ResourceModel\Report\Bestsellers\Collection::class;
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
                'renderer' => \Magento\Reports\Block\Adminhtml\Sales\Grid\Column\Renderer\Date::class,
                'totals_label' => __('Total'),
                'html_decorators' => ['nobr'],
                'header_css_class' => 'col-period',
                'column_css_class' => 'col-period'
            ]
        );

        $this->addColumn(
            'product_name',
            [
                'header' => __('Product'),
                'index' => 'product_name',
                'type' => 'string',
                'sortable' => false,
                'header_css_class' => 'col-product',
                'column_css_class' => 'col-product'
            ]
        );

        if ($this->getFilterData()->getStoreIds()) {
            $this->setStoreIds(explode(',', $this->getFilterData()->getStoreIds()));
        }
        $currencyCode = $this->getCurrentCurrencyCode();

        $this->addColumn(
            'product_price',
            [
                'header' => __('Price'),
                'type' => 'currency',
                'currency_code' => $currencyCode,
                'index' => 'product_price',
                'sortable' => false,
                'rate' => $this->getRate($currencyCode),
                'header_css_class' => 'col-price',
                'column_css_class' => 'col-price'
            ]
        );

        $this->addColumn(
            'qty_ordered',
            [
                'header' => __('Order Quantity'),
                'index' => 'qty_ordered',
                'type' => 'number',
                'total' => 'sum',
                'sortable' => false,
                'header_css_class' => 'col-qty',
                'column_css_class' => 'col-qty'
            ]
        );

        $this->addExportType('*/*/exportBestsellersCsv', __('CSV'));
        $this->addExportType('*/*/exportBestsellersExcel', __('Excel XML'));

        return parent::_prepareColumns();
    }
}
