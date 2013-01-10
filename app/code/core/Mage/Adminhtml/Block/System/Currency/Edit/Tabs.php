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
 * Currency edit tabs
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_System_Currency_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    protected function _construct()
    {
        parent::_construct();
        $this->setId('currency_edit_tabs');
        $this->setDestElementId('currency_edit_form');
        $this->setTitle(Mage::helper('Mage_Adminhtml_Helper_Data')->__('Currency'));
    }

    protected function _beforeToHtml()
    {
        $this->addTab('general', array(
            'label'     => Mage::helper('Mage_Adminhtml_Helper_Data')->__('General Information'),
            'content'   => $this->getLayout()
                ->createBlock('Mage_Adminhtml_Block_System_Currency_Edit_Tab_Main')->toHtml(),
            'active'    => true
        ));

        $this->addTab('currency_rates', array(
            'label'     => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Rates'),
            'content'   => $this->getLayout()
                ->createBlock('Mage_Adminhtml_Block_System_Currency_Edit_Tab_Rates')->toHtml(),
        ));
        
        return parent::_beforeToHtml();
    }
}
