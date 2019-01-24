<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Block\Adminhtml\System\Config\MultiSelect;

use Magento\Paypal\Model\Config\StructurePlugin;

/**
 * Class DisabledFundingOptions

 * @package Magento\Paypal\Block\Adminhtml\System\Config\MultiSelect
 */
class DisabledFundingOptions extends \Magento\Config\Block\System\Config\Form\Field
{
    const FIELD_CONFIG_PATH = 'general/country/default';
    /**
     * DisabledFundingOptions constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Render country field considering request parameter
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        if (!$this->isSelectedMerchantCountry('US')) {
            $fundingOptions = $element->getValues();
            $element->setValues($this->filterValuesForPaypalCredit($fundingOptions));
        }
        return parent::render($element);
    }

    /**
     * Filters array for PAYPAL_FUNDING_CREDIT
     *
     * @param array $options
     * @return array
     */
    private function filterValuesForPaypalCredit($options)
    {
        return array_filter($options, function ($opt) {
            return ($opt['value'] != 'CREDIT');
        });
    }

    /**
     * Checks for chosen Merchant country from the config/url
     *
     * @param string $country
     * @return bool
     */
    private function isSelectedMerchantCountry($country): bool
    {
        $paypalCountry = $this->getRequest()->getParam(StructurePlugin::REQUEST_PARAM_COUNTRY);
        $defaultCountry = $this->_scopeConfig->getValue(self::FIELD_CONFIG_PATH);
        if ($paypalCountry) {
            return $paypalCountry === $country;
        }
        return $defaultCountry === $country;
    }
}
