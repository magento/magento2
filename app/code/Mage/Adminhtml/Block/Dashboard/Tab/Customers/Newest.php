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
 * Adminhtml dashboard most recent customers grid
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Mage_Adminhtml_Block_Dashboard_Tab_Customers_Newest extends Mage_Adminhtml_Block_Dashboard_Grid
{

    protected function _construct()
    {
        parent::_construct();
        $this->setId('customersNewestGrid');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('Mage_Reports_Model_Resource_Customer_Collection')
            ->addCustomerName();

        $storeFilter = 0;
        if ($this->getParam('store')) {
            $collection->addAttributeToFilter('store_id', $this->getParam('store'));
            $storeFilter = 1;
        } else if ($this->getParam('website')){
            $storeIds = Mage::app()->getWebsite($this->getParam('website'))->getStoreIds();
            $collection->addAttributeToFilter('store_id', array('in' => $storeIds));
        } else if ($this->getParam('group')){
            $storeIds = Mage::app()->getGroup($this->getParam('group'))->getStoreIds();
            $collection->addAttributeToFilter('store_id', array('in' => $storeIds));
        }

        $collection->addOrdersStatistics($storeFilter)
            ->orderByCustomerRegistration();

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('name', array(
            'header'    => $this->__('Customer'),
            'sortable'  => false,
            'index'     => 'name'
        ));

        $this->addColumn('orders_count', array(
            'header'    => $this->__('Orders'),
            'sortable'  => false,
            'index'     => 'orders_count',
            'type'      => 'number'
        ));

        $baseCurrencyCode = (string) Mage::app()->getStore((int)$this->getParam('store'))->getBaseCurrencyCode();

        $this->addColumn('orders_avg_amount', array(
            'header'    => $this->__('Average'),
            'align'     => 'right',
            'sortable'  => false,
            'type'      => 'currency',
            'currency_code'  => $baseCurrencyCode,
            'index'     => 'orders_avg_amount',
            'renderer'  =>'Mage_Adminhtml_Block_Report_Grid_Column_Renderer_Currency'
        ));

        $this->addColumn('orders_sum_amount', array(
            'header'    => $this->__('Total'),
            'align'     => 'right',
            'sortable'  => false,
            'type'      => 'currency',
            'currency_code'  => $baseCurrencyCode,
            'index'     => 'orders_sum_amount',
            'renderer'  =>'Mage_Adminhtml_Block_Report_Grid_Column_Renderer_Currency'
        ));

        $this->setFilterVisibility(false);
        $this->setPagerVisibility(false);

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/customer/edit', array('id'=>$row->getId()));
    }
}
