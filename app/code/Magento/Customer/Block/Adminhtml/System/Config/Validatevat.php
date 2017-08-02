<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Adminhtml VAT ID validation block
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Customer\Block\Adminhtml\System\Config;

/**
 * Class \Magento\Customer\Block\Adminhtml\System\Config\Validatevat
 *
 * @since 2.0.0
 */
class Validatevat extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * Merchant Country Field Name
     *
     * @var string
     * @since 2.0.0
     */
    protected $_merchantCountry = 'general_store_information_country_id';

    /**
     * Merchant VAT Number Field
     *
     * @var string
     * @since 2.0.0
     */
    protected $_merchantVatNumber = 'general_store_information_merchant_vat_number';

    /**
     * Validate VAT Button Label
     *
     * @var string
     * @since 2.0.0
     */
    protected $_vatButtonLabel = 'Validate VAT Number';

    /**
     * Set Merchant Country Field Name
     *
     * @param string $countryField
     * @return \Magento\Customer\Block\Adminhtml\System\Config\Validatevat
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $originalData = $element->getOriginalData();
        $buttonLabel = !empty($originalData['button_label']) ? $originalData['button_label'] : $this->_vatButtonLabel;
        $this->addData(
            [
                'button_label' => __($buttonLabel),
                'html_id' => $element->getHtmlId(),
                'ajax_url' => $this->_urlBuilder->getUrl('customer/system_config_validatevat/validate'),
            ]
        );

        return $this->_toHtml();
    }
}
