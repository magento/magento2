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
 * @package     Mage_Paypal
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml paypal settlement reports grid block
 *
 * @category    Mage
 * @package     Mage_Paypal
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Paypal_Block_Adminhtml_Settlement_Report extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * Prepare grid container, add additional buttons
     */
    public function __construct()
    {
        $this->_blockGroup = 'Mage_Paypal';
        $this->_controller = 'adminhtml_settlement_report';
        $this->_headerText = Mage::helper('Mage_Paypal_Helper_Data')->__('PayPal Settlement Reports');
        parent::__construct();
        $this->_removeButton('add');
        $message = Mage::helper('Mage_Paypal_Helper_Data')->__('Connecting to PayPal SFTP server to fetch new reports. Are you sure you want to proceed?');
        $this->_addButton('fetch', array(
            'label'   => Mage::helper('Mage_Paypal_Helper_Data')->__('Fetch Updates'),
            'onclick' => "confirmSetLocation('{$message}', '{$this->getUrl('*/*/fetch')}')",
            'class'   => 'task'
        ));
    }
}
