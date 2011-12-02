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
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Sign up for an alert when the product price changes grid
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Alerts_Price extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();

        $this->setId('alertPrice');
        $this->setDefaultSort('add_date');
        $this->setDefaultSort('DESC');
        $this->setUseAjax(true);
        $this->setFilterVisibility(false);
        $this->setEmptyText(Mage::helper('Mage_Catalog_Helper_Data')->__('There are no customers for this alert'));
    }

    protected function _prepareCollection()
    {
        $productId = $this->getRequest()->getParam('id');
        $websiteId = 0;
        if ($store = $this->getRequest()->getParam('store')) {
            $websiteId = Mage::app()->getStore($store)->getWebsiteId();
        }
        if (Mage::helper('Mage_Catalog_Helper_Data')->isModuleEnabled('Mage_ProductAlert')) {
            $collection = Mage::getModel('Mage_ProductAlert_Model_Price')
                ->getCustomerCollection()
                ->join($productId, $websiteId);
            $this->setCollection($collection);
        }
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('firstname', array(
            'header'    => Mage::helper('Mage_Catalog_Helper_Data')->__('First Name'),
            'index'     => 'firstname',
        ));

        $this->addColumn('lastname', array(
            'header'    => Mage::helper('Mage_Catalog_Helper_Data')->__('Last Name'),
            'index'     => 'lastname',
        ));

        $this->addColumn('email', array(
            'header'    => Mage::helper('Mage_Catalog_Helper_Data')->__('Email'),
            'index'     => 'email',
        ));

        $this->addColumn('price', array(
            'header'    => Mage::helper('Mage_Catalog_Helper_Data')->__('Price'),
            'index'     => 'price',
            'type'      => 'currency',
            'currency_code'
                        => Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE)
        ));

        $this->addColumn('add_date', array(
            'header'    => Mage::helper('Mage_Catalog_Helper_Data')->__('Date Subscribed'),
            'index'     => 'add_date',
            'type'      => 'date'
        ));

        $this->addColumn('last_send_date', array(
            'header'    => Mage::helper('Mage_Catalog_Helper_Data')->__('Last Notification'),
            'index'     => 'last_send_date',
            'type'      => 'date'
        ));

        $this->addColumn('send_count', array(
            'header'    => Mage::helper('Mage_Catalog_Helper_Data')->__('Send Count'),
            'index'     => 'send_count',
        ));

        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        $productId = $this->getRequest()->getParam('id');
        $storeId   = $this->getRequest()->getParam('store', 0);
        if ($storeId) {
            $storeId = Mage::app()->getStore($storeId)->getId();
        }
        return $this->getUrl('*/catalog_product/alertsPriceGrid', array(
            'id'    => $productId,
            'store' => $storeId
        ));
    }
}
