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
 * Adminhtml VAT ID validation block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Customer_System_Config_Validatevat extends Mage_Backend_Block_System_Config_Form_Field
{
    /**
     * Merchant Country Field Name
     *
     * @var string
     */
    protected $_merchantCountry = 'general_store_information_country_id';

    /**
     * Merchant VAT Number Field
     *
     * @var string
     */
    protected $_merchantVatNumber = 'general_store_information_merchant_vat_number';

    /**
     * Validate VAT Button Label
     *
     * @var string
     */
    protected $_vatButtonLabel = 'Validate VAT Number';

    /**
     * Set Merchant Country Field Name
     *
     * @param string $countryField
     * @return Mage_Adminhtml_Block_Customer_System_Config_Validatevat
     */
    public function setMerchantCountryField($countryField)
    {
        $this->_merchantCountry = $countryField;
        return $this;
    }

    /**
     * Get Merchant Country Field Name
     *
     * @return string
     */
    public function getMerchantCountryField()
    {
        return $this->_merchantCountry;
    }

    /**
     * Set Merchant VAT Number Field
     *
     * @param string $vatNumberField
     * @return Mage_Adminhtml_Block_Customer_System_Config_Validatevat
     */
    public function setMerchantVatNumberField($vatNumberField)
    {
        $this->_merchantVatNumber = $vatNumberField;
        return $this;
    }

    /**
     * Get Merchant VAT Number Field
     *
     * @return string
     */
    public function getMerchantVatNumberField()
    {
        return $this->_merchantVatNumber;
    }

    /**
     * Set Validate VAT Button Label
     *
     * @param string $vatButtonLabel
     * @return Mage_Adminhtml_Block_Customer_System_Config_Validatevat
     */
    public function setVatButtonLabel($vatButtonLabel)
    {
        $this->_vatButtonLabel = $vatButtonLabel;
        return $this;
    }

    /**
     * Set template to itself
     *
     * @return Mage_Adminhtml_Block_Customer_System_Config_Validatevat
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate('customer/system/config/validatevat.phtml');
        }
        return $this;
    }

    /**
     * Unset some non-related element parameters
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Get the button and scripts contents
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $originalData = $element->getOriginalData();
        $buttonLabel = !empty($originalData['button_label']) ? $originalData['button_label'] : $this->_vatButtonLabel;
        $this->addData(array(
            'button_label' => Mage::helper('Mage_Customer_Helper_Data')->__($buttonLabel),
            'html_id' => $element->getHtmlId(),
            'ajax_url' => Mage::getSingleton('Mage_Backend_Model_Url')
                ->getUrl('adminhtml/customer_system_config_validatevat/validate')
        ));

        return $this->_toHtml();
    }
}
