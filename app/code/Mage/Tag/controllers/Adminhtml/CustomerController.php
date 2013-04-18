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
 * @package     Mage_Tag
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Controller to process customer tag actions
 *
 * @category    Mage
 * @package     Mage_Tag
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Tag_Adminhtml_CustomerController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Adds to registry current customer instance
     *
     * @param string $idFieldName
     * @return Mage_Tag_Adminhtml_CustomerController
     */
    protected function _initCustomer($idFieldName = 'id')
    {
        $this->_title($this->__('Customers'))->_title($this->__('Manage Customers'));

        $customerId = (int) $this->getRequest()->getParam($idFieldName);
        $customer   = Mage::getModel('Mage_Customer_Model_Customer');

        if ($customerId) {
            $customer->load($customerId);
        }

        Mage::register('current_customer', $customer);
        return $this;
    }

    /**
     * Processes ajax action to render tags tab content
     */
    public function productTagsAction()
    {
        $this->_initCustomer();

        /** @var $customer Mage_Customer_Model_Customer */
        $customer = Mage::registry('current_customer');
        /** @var $block Mage_Tag_Block_Adminhtml_Customer_Edit_Tab_Tag_Grid */
        $block = $this->loadLayout()
            ->getLayout()
            ->getBlock('admin.customer.tags');
        $block->setCustomerId($customer->getId())
            ->setUseAjax(true);

        $this->renderLayout();
    }

    /**
     * Processes tag grid actions
     */
    public function tagGridAction()
    {
        $this->_initCustomer();

        /** @var $block Mage_Tag_Block_Adminhtml_Customer_Edit_Tab_Tag_Grid */
        $block = $this->loadLayout()
            ->getLayout()
            ->getBlock('admin.customer.tags');
        $block->setCustomerId(Mage::registry('current_customer'));

        $this->renderLayout();
    }
}
