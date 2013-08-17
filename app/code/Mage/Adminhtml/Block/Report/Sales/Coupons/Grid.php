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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml coupons report grid block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Report_Sales_Coupons_Grid extends Mage_Adminhtml_Block_Report_Grid_Abstract
{
    protected $_columnGroupBy = 'period';

    protected function _construct()
    {
        parent::_construct();
        $this->setCountTotals(true);
        $this->setCountSubTotals(true);
    }

    public function getResourceCollectionName()
    {
        if (($this->getFilterData()->getData('report_type') == 'updated_at_order')) {
            return 'Mage_SalesRule_Model_Resource_Report_Updatedat_Collection';
        } else {
            return 'Mage_SalesRule_Model_Resource_Report_Collection';
        }
    }

    protected function _prepareColumns()
    {
        $this->addColumn('period', array(
            'header'            => Mage::helper('Mage_SalesRule_Helper_Data')->__('Interval'),
            'index'             => 'period',
            'sortable'          => false,
            'period_type'       => $this->getPeriodType(),
            'renderer'          => 'Mage_Adminhtml_Block_Report_Sales_Grid_Column_Renderer_Date',
            'totals_label'      => Mage::helper('Mage_SalesRule_Helper_Data')->__('Total'),
            'subtotals_label'   => Mage::helper('Mage_SalesRule_Helper_Data')->__('Subtotal'),
            'html_decorators' => array('nobr'),
            'header_css_class'  => 'col-period',
            'column_css_class'  => 'col-period'
        ));

        $this->addColumn('coupon_code', array(
            'header'    => Mage::helper('Mage_SalesRule_Helper_Data')->__('Coupon Code'),
            'sortable'  => false,
            'index'     => 'coupon_code',
            'header_css_class'  => 'col-code',
            'column_css_class'  => 'col-code'
        ));

        $this->addColumn('rule_name', array(
            'header'    => Mage::helper('Mage_SalesRule_Helper_Data')->__('Price Rule'),
            'sortable'  => false,
            'index'     => 'rule_name',
            'header_css_class'  => 'col-rule',
            'column_css_class'  => 'col-rule'
        ));

        $this->addColumn('coupon_uses', array(
            'header'    => Mage::helper('Mage_SalesRule_Helper_Data')->__('Uses'),
            'sortable'  => false,
            'index'     => 'coupon_uses',
            'total'     => 'sum',
            'type'      => 'number',
            'header_css_class'  => 'col-users',
            'column_css_class'  => 'col-users'
        ));

        if ($this->getFilterData()->getStoreIds()) {
            $this->setStoreIds(explode(',', $this->getFilterData()->getStoreIds()));
        }
        $currencyCode = $this->getCurrentCurrencyCode();
        $rate = $this->getRate($currencyCode);

        $this->addColumn('subtotal_amount', array(
            'header'        => Mage::helper('Mage_SalesRule_Helper_Data')->__('Sales Subtotal'),
            'sortable'      => false,
            'type'          => 'currency',
            'currency_code' => $currencyCode,
            'total'         => 'sum',
            'index'         => 'subtotal_amount',
            'rate'          => $rate,
            'header_css_class'  => 'col-sales',
            'column_css_class'  => 'col-sales'
        ));

        $this->addColumn('discount_amount', array(
            'header'        => Mage::helper('Mage_SalesRule_Helper_Data')->__('Sales Discount'),
            'sortable'      => false,
            'type'          => 'currency',
            'currency_code' => $currencyCode,
            'total'         => 'sum',
            'index'         => 'discount_amount',
            'rate'          => $rate,
            'header_css_class'  => 'col-sales-discount',
            'column_css_class'  => 'col-sales-discount'
        ));

        $this->addColumn('total_amount', array(
            'header'        => Mage::helper('Mage_SalesRule_Helper_Data')->__('Sales Total'),
            'sortable'      => false,
            'type'          => 'currency',
            'currency_code' => $currencyCode,
            'total'         => 'sum',
            'index'         => 'total_amount',
            'rate'          => $rate,
            'header_css_class'  => 'col-total-amount',
            'column_css_class'  => 'col-total-amount'
        ));

        $this->addColumn('subtotal_amount_actual', array(
            'header'        => Mage::helper('Mage_SalesRule_Helper_Data')->__('Subtotal'),
            'sortable'      => false,
            'type'          => 'currency',
            'currency_code' => $currencyCode,
            'total'         => 'sum',
            'index'         => 'subtotal_amount_actual',
            'rate'          => $rate,
            'header_css_class'  => 'col-subtotal',
            'column_css_class'  => 'col-subtotal'
        ));

        $this->addColumn('discount_amount_actual', array(
            'header'        => Mage::helper('Mage_SalesRule_Helper_Data')->__('Discount'),
            'sortable'      => false,
            'type'          => 'currency',
            'currency_code' => $currencyCode,
            'total'         => 'sum',
            'index'         => 'discount_amount_actual',
            'rate'          => $rate,
            'header_css_class'  => 'col-discount',
            'column_css_class'  => 'col-discount'
        ));

        $this->addColumn('total_amount_actual', array(
            'header'        => Mage::helper('Mage_SalesRule_Helper_Data')->__('Total'),
            'sortable'      => false,
            'type'          => 'currency',
            'currency_code' => $currencyCode,
            'total'         => 'sum',
            'index'         => 'total_amount_actual',
            'rate'          => $rate,
            'header_css_class'  => 'col-total',
            'column_css_class'  => 'col-total'
        ));

        $this->addExportType('*/*/exportCouponsCsv', Mage::helper('Mage_Adminhtml_Helper_Data')->__('CSV'));
        $this->addExportType('*/*/exportCouponsExcel', Mage::helper('Mage_Adminhtml_Helper_Data')->__('Excel XML'));

        return parent::_prepareColumns();
    }

    /**
     * Add price rule filter
     *
     * @param Mage_Reports_Model_Resource_Report_Collection_Abstract $collection
     * @param Varien_Object $filterData
     * @return Mage_Adminhtml_Block_Report_Grid_Abstract
     */
    protected function _addCustomFilter($collection, $filterData)
    {
        if ($filterData->getPriceRuleType()) {
            $rulesList = $filterData->getData('rules_list');
            if (isset($rulesList[0])) {
                $rulesIds = explode(',', $rulesList[0]);
                $collection->addRuleFilter($rulesIds);
            }
        }

        return parent::_addCustomFilter($filterData, $collection);
    }
}
