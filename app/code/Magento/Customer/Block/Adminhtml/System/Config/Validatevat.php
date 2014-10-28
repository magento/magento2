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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml VAT ID validation block
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Customer\Block\Adminhtml\System\Config;

class Validatevat extends \Magento\Backend\Block\System\Config\Form\Field
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
     * @return \Magento\Customer\Block\Adminhtml\System\Config\Validatevat
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
     * @return \Magento\Customer\Block\Adminhtml\System\Config\Validatevat
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
     * @return \Magento\Customer\Block\Adminhtml\System\Config\Validatevat
     */
    public function setVatButtonLabel($vatButtonLabel)
    {
        $this->_vatButtonLabel = $vatButtonLabel;
        return $this;
    }

    /**
     * Set template to itself
     *
     * @return \Magento\Customer\Block\Adminhtml\System\Config\Validatevat
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate('system/config/validatevat.phtml');
        }
        return $this;
    }

    /**
     * Unset some non-related element parameters
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Get the button and scripts contents
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $originalData = $element->getOriginalData();
        $buttonLabel = !empty($originalData['button_label']) ? $originalData['button_label'] : $this->_vatButtonLabel;
        $this->addData(
            array(
                'button_label' => __($buttonLabel),
                'html_id' => $element->getHtmlId(),
                'ajax_url' => $this->_urlBuilder->getUrl('customer/system_config_validatevat/validate')
            )
        );

        return $this->_toHtml();
    }
}
