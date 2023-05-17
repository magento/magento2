<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Paypal\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Store\Model\ScopeInterface;

class PayLaterLink extends Field
{
    /**
     * Location of the PayPal's "Merchant Country" config param
     */
    private const XML_PATH_PAYPAL_MERCHANT_COUNTRY = 'paypal/general/merchant_country';

    /**
     * Default country is set as US.
     */
    private const DEFAULT_COUNTRY = 'US';

    /**
     * @var array.
     */
    private const ARRAY_PAYLATER_SUPPORTED_COUNTRIES = ['US','GB','DE','FR','AU','IT','ES'];

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param Context $context
     * @param ScopeConfigInterface $scopeConfig
     * @param array $data
     */
    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @inheritdoc
     */
    public function _getElementHtml(AbstractElement $element): string
    {
        $html = parent::_getElementHtml($element);
        $html .= $this->getPayLaterCommentHtml();
        $country = $this->getPayLaterCountry();

        return (in_array($country, self::ARRAY_PAYLATER_SUPPORTED_COUNTRIES)) ?
            sprintf($html, strtolower($country)) : sprintf($html, strtolower(self::DEFAULT_COUNTRY));
    }

    /**
     * Get pay later merchant country
     *
     * @return string
     */
    private function getPayLaterCountry(): string
    {
        return $this->getRequest()->getParam('paypal_country') ?: ($this->scopeConfig->getValue(
            self::XML_PATH_PAYPAL_MERCHANT_COUNTRY,
            ScopeInterface::SCOPE_STORES
        ) ?: self::DEFAULT_COUNTRY);
    }

    /**
     * Get pay later comment html
     *
     * @return string
     */
    private function getPayLaterCommentHtml(): string
    {
        return '<p class="note">
        Displays Pay Later messaging for available offers. Restrictions apply. Click
        <a href="https://developer.paypal.com/docs/business/pay-later/%s/commerce-platforms/magento2/magento-paypal/"
        target="_blank">here</a> to learn more</p>';
    }
}
