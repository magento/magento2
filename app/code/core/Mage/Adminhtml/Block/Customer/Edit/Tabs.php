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
 * admin customer left menu
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Customer_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    protected function _construct()
    {
        parent::_construct();
        $this->setId('customer_info_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('Mage_Customer_Helper_Data')->__('Customer Information'));
    }

    protected function _beforeToHtml()
    {
        Magento_Profiler::start('customer/tabs');

        /*
                if (Mage::registry('current_customer')->getId()) {
                    $this->addTab('view', array(
                        'label'     => Mage::helper('Mage_Customer_Helper_Data')->__('Customer View'),
                        'content'   => $this->getLayout()
                        ->createBlock('Mage_Adminhtml_Block_Customer_Edit_Tab_View')->toHtml(),
                        'active'    => true
                    ));
                }
        */
        $this->addTab('account', array(
            'label'     => Mage::helper('Mage_Customer_Helper_Data')->__('Account Information'),
            'content'   => $this->getLayout()
                ->createBlock('Mage_Adminhtml_Block_Customer_Edit_Tab_Account')->initForm()->toHtml(),
            'active'    => Mage::registry('current_customer')->getId() ? false : true
        ));

        $this->addTab('addresses', array(
            'label'     => Mage::helper('Mage_Customer_Helper_Data')->__('Addresses'),
            'content'   => $this->getLayout()
                ->createBlock('Mage_Adminhtml_Block_Customer_Edit_Tab_Addresses')->initForm()->toHtml(),
        ));


        // load: Orders, Shopping Cart, Wishlist, Product Reviews, Product Tags - with ajax

        if (Mage::registry('current_customer')->getId()) {

            if (Mage::getSingleton('Mage_Core_Model_Authorization')->isAllowed('Mage_Sales::actions_view')) {
                $this->addTab('orders', array(
                    'label'     => Mage::helper('Mage_Customer_Helper_Data')->__('Orders'),
                    'class'     => 'ajax',
                    'url'       => $this->getUrl('*/*/orders', array('_current' => true)),
                 ));
            }

            $this->addTab('cart', array(
                'label'     => Mage::helper('Mage_Customer_Helper_Data')->__('Shopping Cart'),
                'class'     => 'ajax',
                'url'       => $this->getUrl('*/*/carts', array('_current' => true)),
            ));

            $this->addTab('wishlist', array(
                'label'     => Mage::helper('Mage_Customer_Helper_Data')->__('Wishlist'),
                'class'     => 'ajax',
                'url'       => $this->getUrl('*/*/wishlist', array('_current' => true)),
            ));

            if (Mage::getSingleton('Mage_Core_Model_Authorization')->isAllowed('Mage_Newsletter::subscriber')) {
                $this->addTab('newsletter', array(
                    'label'     => Mage::helper('Mage_Customer_Helper_Data')->__('Newsletter'),
                    'content'   => $this->getLayout()
                        ->createBlock('Mage_Adminhtml_Block_Customer_Edit_Tab_Newsletter')->initForm()->toHtml()
                ));
            }

            if (Mage::getSingleton('Mage_Core_Model_Authorization')->isAllowed('Mage_Review::reviews_ratings')) {
                $this->addTab('reviews', array(
                    'label'     => Mage::helper('Mage_Customer_Helper_Data')->__('Product Reviews'),
                    'class'     => 'ajax',
                    'url'       => $this->getUrl('*/*/productReviews', array('_current' => true)),
                ));
            }
        }

        $this->_updateActiveTab();
        Magento_Profiler::stop('customer/tabs');
        return parent::_beforeToHtml();
    }

    protected function _updateActiveTab()
    {
        $tabId = $this->getRequest()->getParam('tab');
        if ($tabId) {
            $tabId = preg_replace("#{$this->getId()}_#", '', $tabId);
            if ($tabId) {
                $this->setActiveTab($tabId);
            }
        }
    }
}
