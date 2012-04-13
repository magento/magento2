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
 * @package     Mage_GoogleShopping
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml Google Content Items Grids Container
 *
 * @category   Mage
 * @package    Mage_GoogleShopping
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_GoogleShopping_Block_Adminhtml_Items extends Mage_Adminhtml_Block_Widget_Grid_Container
{

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('items.phtml');
    }

    /**
     * Preparing layout
     *
     * @return Mage_GoogleShopping_Block_Adminhtml_Items
     */
    protected function _prepareLayout()
    {
        $this->setChild(
            'item',
            $this->getLayout()->createBlock('Mage_GoogleShopping_Block_Adminhtml_Items_Item')
        );
        $this->setChild(
            'product',
            $this->getLayout()->createBlock('Mage_GoogleShopping_Block_Adminhtml_Items_Product')
        );
        $this->setChild(
            'store_switcher',
            $this->getLayout()->createBlock('Mage_GoogleShopping_Block_Adminhtml_Store_Switcher')
        );

        return $this;
    }

    /**
     * Get HTML code for Store Switcher select
     *
     * @return string
     */
    public function getStoreSwitcherHtml()
    {
        return $this->getChildHtml('store_switcher');
    }

    /**
     * Get HTML code for CAPTCHA
     *
     * @return string
     */
    public function getCaptchaHtml()
    {
        return $this->getLayout()->createBlock('Mage_GoogleShopping_Block_Adminhtml_Captcha')
            ->setGcontentCaptchaToken($this->getGcontentCaptchaToken())
            ->setGcontentCaptchaUrl($this->getGcontentCaptchaUrl())
            ->toHtml();
    }

    /**
     * Get selecetd store
     *
     * @return Mage_Core_Model_Store
     */
    public function getStore()
    {
        return $this->_getData('store');
    }

    /**
     * Check whether synchronization process is running
     *
     * @return bool
     */
    public function isProcessRunning()
    {
        $flag = Mage::getModel('Mage_GoogleShopping_Model_Flag')->loadSelf();
        return $flag->isLocked();
    }

    /**
     * Build url for retrieving background process status
     *
     * @return string
     */
    public function getStatusUrl()
    {
        return $this->getUrl('*/*/status');
    }
}
