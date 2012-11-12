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
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml invoiced report grid block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Report_Sales_Invoiced_Grid extends Mage_Adminhtml_Block_Report_Grid_Abstract
{
    protected $_columnGroupBy = 'period';

    protected function _construct()
    {
        parent::_construct();
        $this->setCountTotals(true);
    }

    public function getResourceCollectionName()
    {
        return ($this->getFilterData()->getData('report_type') == 'created_at_invoice')
            ? 'Mage_Sales_Model_Resource_Report_Invoiced_Collection_Invoiced'
            : 'Mage_Sales_Model_Resource_Report_Invoiced_Collection_Order';
    }

    protected function _prepareColumns()
    {
        $this->addColumn('period', array(
            'header'        => Mage::helper('Mage_Sales_Helper_Data')->__('Period'),
            'index'         => 'period',
            'width'         => 100,
            'sortable'      => false,
            'period_type'   => $this->getPeriodType(),
            'renderer'      => 'Mage_Adminhtml_Block_Report_Sales_Grid_Column_Renderer_Date',
            'totals_label'  => Mage::helper('Mage_Sales_Helper_Data')->__('Total'),
            'html_decorators' => array('nobr'),
        ));

        $this->addColumn('orders_count', array(
            'header'    => Mage::helper('Mage_Sales_Helper_Data')->__('Number of Orders'),
            'index'     => 'orders_count',
            'type'      => 'number',
            'total'     => 'sum',
            'sortable'  => false
        ));

        $this->addColumn('orders_invoiced', array(
            'header'    => Mage::helper('Mage_Sales_Helper_Data')->__('Number of Invoiced Orders'),
            'index'     => 'orders_invoiced',
            'type'      => 'number',
            'total'     => 'sum',
            'sortable'  => false
        ));

        if ($this->getFilterData()->getStoreIds()) {
            $this->setStoreIds(explode(',', $this->getFilterData()->getStoreIds()));
        }
        $currencyCode = $this->getCurrentCurrencyCode();
        $rate = $this->getRate($currencyCode);

        $this->addColumn('invoiced', array(
            'header'        => Mage::helper('Mage_Sales_Helper_Data')->__('Total Invoiced'),
            'type'          => 'currency',
            'currency_code' => $currencyCode,
            'index'         => 'invoiced',
            'total'         => 'sum',
            'sortable'      => false,
            'rate'          => $rate,
        ));

        $this->addColumn('invoiced_captured', array(
            'header'        => Mage::helper('Mage_Sales_Helper_Data')->__('Total Invoiced Paid'),
            'type'          => 'currency',
            'currency_code' => $currencyCode,
            'index'         => 'invoiced_captured',
            'total'         => 'sum',
            'sortable'      => false,
            'rate'          => $rate,
        ));

        $this->addColumn('invoiced_not_captured', array(
            'header'        => Mage::helper('Mage_Sales_Helper_Data')->__('Total Invoiced not Paid'),
            'type'          => 'currency',
            'currency_code' => $currencyCode,
            'index'         => 'invoiced_not_captured',
            'total'         => 'sum',
            'sortable'      => false,
            'rate'          => $rate,
        ));

        $this->addExportType('*/*/exportInvoicedCsv', Mage::helper('Mage_Adminhtml_Helper_Data')->__('CSV'));
        $this->addExportType('*/*/exportInvoicedExcel', Mage::helper('Mage_Adminhtml_Helper_Data')->__('Excel XML'));

        return parent::_prepareColumns();
    }
}
