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
 * Manage currency import services block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_CurrencySymbol_Block_Adminhtml_System_Currency_Rate_Services extends Mage_Backend_Block_Template
{
    protected $_template = 'system/currency/rate/services.phtml';

    /**
     * Create import services form select element
     *
     * @return Mage_Core_Block_Abstract
     */
    protected function _prepareLayout()
    {
        $this->setChild(
            'import_services',
            $this->getLayout()->createBlock('Mage_Adminhtml_Block_Html_Select')
                ->setOptions(Mage::getModel('Mage_Backend_Model_Config_Source_Currency_Service')->toOptionArray(0))
                ->setId('rate_services')
                ->setName('rate_services')
                ->setValue(Mage::getSingleton('Mage_Adminhtml_Model_Session')->getCurrencyRateService(true))
                ->setTitle(Mage::helper('Mage_Adminhtml_Helper_Data')->__('Import Service'))
        );

        return parent::_prepareLayout();
    }

}
