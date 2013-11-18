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
 * @category    Magento
 * @package     Magento_Adminhtml
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml tax report grid block
 *
 * @category   Magento
 * @package    Magento_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Adminhtml\Block\Report\Sales\Tax;

class Grid extends \Magento\Adminhtml\Block\Report\Grid\AbstractGrid
{
    protected $_columnGroupBy = 'period';

    /**
     * @var \Magento\Sales\Model\Order\ConfigFactory
     */
    protected $_configFactory;

    /**
     * @param \Magento\Sales\Model\Order\ConfigFactory $configFactory
     * @param \Magento\Reports\Model\Resource\Report\Collection\Factory $resourceFactory
     * @param \Magento\Reports\Model\Grouped\CollectionFactory $collectionFactory
     * @param \Magento\Reports\Helper\Data $reportsData
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\Core\Model\Url $urlModel
     * @param array $data
     */
    public function __construct(
        \Magento\Sales\Model\Order\ConfigFactory $configFactory,
        \Magento\Reports\Model\Resource\Report\Collection\Factory $resourceFactory,
        \Magento\Reports\Model\Grouped\CollectionFactory $collectionFactory,
        \Magento\Reports\Helper\Data $reportsData,
        \Magento\Core\Helper\Data $coreData,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\Core\Model\Url $urlModel,
        array $data = array()
    ) {
        $this->_configFactory = $configFactory;
        parent::__construct(
            $resourceFactory, $collectionFactory, $reportsData, $coreData, $context, $storeManager, $urlModel, $data
        );
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setCountTotals(true);
        $this->setCountSubTotals(true);
    }

    public function getResourceCollectionName()
    {
        return ($this->getFilterData()->getData('report_type') == 'updated_at_order')
            ? 'Magento\Tax\Model\Resource\Report\Updatedat\Collection'
            : 'Magento\Tax\Model\Resource\Report\Collection';
    }

    protected function _prepareColumns()
    {
        $this->addColumn('period', array(
            'header'            => __('Interval'),
            'index'             => 'period',
            'sortable'          => false,
            'period_type'       => $this->getPeriodType(),
            'renderer'          => 'Magento\Adminhtml\Block\Report\Sales\Grid\Column\Renderer\Date',
            'totals_label'      => __('Total'),
            'subtotals_label'   => __('Subtotal'),
            'html_decorators' => array('nobr'),
            'header_css_class'  => 'col-period',
            'column_css_class'  => 'col-period'
        ));

        $this->addColumn('code', array(
            'header'    => __('Tax'),
            'index'     => 'code',
            'type'      => 'string',
            'sortable'  => false,
            'header_css_class'  => 'col-tax-name',
            'column_css_class'  => 'col-tax-name'
        ));

        $this->addColumn('percent', array(
            'header'    => __('Rate'),
            'index'     => 'percent',
            'type'      => 'number',
            'sortable'  => false,
            'header_css_class'  => 'col-rate',
            'column_css_class'  => 'col-rate'
        ));

        $this->addColumn('orders_count', array(
            'header'    => __('Orders'),
            'index'     => 'orders_count',
            'total'     => 'sum',
            'type'      => 'number',
            'sortable'  => false,
            'header_css_class'  => 'col-qty',
            'column_css_class'  => 'col-qty'
        ));

        if ($this->getFilterData()->getStoreIds()) {
            $this->setStoreIds(explode(',', $this->getFilterData()->getStoreIds()));
        }
        $currencyCode = $this->getCurrentCurrencyCode();

        $this->addColumn('tax_base_amount_sum', array(
            'header'        => __('Tax Amount'),
            'type'          => 'currency',
            'currency_code' => $currencyCode,
            'index'         => 'tax_base_amount_sum',
            'total'         => 'sum',
            'sortable'      => false,
            'rate'          => $this->getRate($currencyCode),
            'header_css_class'  => 'col-tax-amount',
            'column_css_class'  => 'col-tax-amount'
        ));

        $this->addExportType('*/*/exportTaxCsv', __('CSV'));
        $this->addExportType('*/*/exportTaxExcel', __('Excel XML'));

        return parent::_prepareColumns();
    }

    /**
     * Preparing collection
     * Filter canceled statuses for orders in taxes
     *
     * @return \Magento\Adminhtml\Block\Report\Sales\Tax\Grid
     */
    protected function _prepareCollection()
    {
        $filterData = $this->getFilterData();
        if (!$filterData->hasData('order_statuses')) {
            $orderConfig = $this->_configFactory->create();
            $statusValues = array();
            $canceledStatuses = $orderConfig->getStateStatuses(\Magento\Sales\Model\Order::STATE_CANCELED);
            foreach ($orderConfig->getStatuses() as $code => $label) {
                if (!isset($canceledStatuses[$code])) {
                    $statusValues[] = $code;
                }
            }
            $filterData->setOrderStatuses($statusValues);
        }
        return parent::_prepareCollection();
    }
}
