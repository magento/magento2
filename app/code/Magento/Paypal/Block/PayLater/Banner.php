<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Paypal\Block\PayLater;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Paypal\Block\Adminhtml\System\Config\PayLaterLink;
use Magento\Paypal\Model\PayLaterConfig;
use Magento\Paypal\Model\SdkUrl;
use Magento\Paypal\Model\Config as PaypalConfig;
use Magento\Framework\App\ObjectManager;
use Magento\Customer\Model\Session as CustomerSession;

/**
 * PayPal PayLater component block
 * @api
 */
class Banner extends Template
{
    /**
     * @var PayLaterConfig
     */
    private $payLaterConfig;

    /**
     * @var SdkUrl
     */
    private $sdkUrl;

    /**
     * @var string
     */
    private $placement = '';

    /**
     * @var string
     */
    private $position = '';

    /**
     * @var PaypalConfig
     */
    private $paypalConfig;

    /**
     * @var string|null
     */
    private ?string $buyerCountry = null;

    /**
     * @var CustomerSession
     */
    private CustomerSession $session;

    /**
     * @param Context $context
     * @param PayLaterConfig $payLaterConfig
     * @param SdkUrl $sdkUrl
     * @param array $data
     * @param PaypalConfig|null $paypalConfig
     * @param CustomerSession|null $session
     */
    public function __construct(
        Template\Context $context,
        PayLaterConfig $payLaterConfig,
        SdkUrl $sdkUrl,
        array $data = [],
        ?PaypalConfig $paypalConfig = null,
        ?CustomerSession $session = null
    ) {
        parent::__construct($context, $data);
        $this->payLaterConfig = $payLaterConfig;
        $this->sdkUrl = $sdkUrl;
        $this->placement = $data['placement'] ??  '';
        $this->position = $data['position'] ??  '';
        $this->paypalConfig = $paypalConfig ?: ObjectManager::getInstance()
            ->get(PaypalConfig::class);
        $this->session = $session ?: ObjectManager::getInstance()->get(CustomerSession::class);
    }

    /**
     * Disable block output
     *
     * @return string
     */
    protected function _toHtml(): string
    {
        if (!$this->isEnabled()) {
            return '';
        }
        return parent::_toHtml();
    }

    /**
     * @inheritdoc
     */
    public function getJsLayout()
    {
        $jsComponent = $this->jsLayout['components']['payLater']['component']
            ?? 'Magento_Paypal/js/view/paylater';

        //Extend block component config with defaults
        $componentConfig = $this->jsLayout['components']['payLater']['config'] ?? [];
        $defaultConfig = ['sdkUrl' => $this->getPayPalSdkUrl()];
        $config = array_replace($defaultConfig, $componentConfig);
        $displayAmount = $config['displayAmount'] ?? false;
        $config['displayAmount'] = !$displayAmount || $this->payLaterConfig->isPPBillingAgreementEnabled()
            ? false : true;
        $config['dataAttributes'] = [
            'data-partner-attribution-id' => $this->paypalConfig->getBuildNotationCode(),
            'data-csp-nonce' => $this->paypalConfig->getCspNonce(),
        ];

        //Extend block component attributes with defaults
        $componentAttributes = $this->jsLayout['components']['payLater']['config']['attributes'] ?? [];
        $config['attributes'] = array_replace($this->getStyleAttributesConfig(), $componentAttributes);
        $config['attributes']['data-pp-placement'] = $this->placement;
        $config['attributes']['data-pp-buyercountry'] = $this->getBuyerCountry();

        $this->jsLayout = [
            'components' => [
                'payLater' => [
                    'component' => $jsComponent,
                    'config' => $config
                ]
            ]
        ];

        return parent::getJsLayout();
    }

    /**
     * Build\Get URL to PP SDK
     *
     * @return string
     */
    private function getPayPalSdkUrl(): string
    {
        return $this->sdkUrl->getUrl();
    }

    /**
     * Retrieve style configuration
     *
     * @return string[]
     */
    private function getStyleAttributesConfig(): array
    {
        return $this->payLaterConfig->getSectionConfig($this->placement, PayLaterConfig::CONFIG_KEY_STYLE);
    }

    /**
     * Check if block should be displayed
     *
     * @return bool
     */
    private function isEnabled(): bool
    {
        $enabled = $this->payLaterConfig->isEnabled($this->placement);
        return $enabled &&
            $this->isBuyerCountryAvailable() &&
            $this->payLaterConfig->getSectionConfig($this->placement, PayLaterConfig::CONFIG_KEY_POSITION) ===
                $this->position;
    }

    /**
     * The pay later message should be displayed only if the buyer country is available
     *
     * @return bool
     */
    private function isBuyerCountryAvailable(): bool
    {
        return (bool)$this->getBuyerCountry();
    }

    /**
     * Buyer country should be extracted from logged-in user billing or shipping address
     *
     * @return string
     */
    private function getBuyerCountry(): string
    {
        if ($this->buyerCountry === null) {
            $country = null;
            if ($this->session->isLoggedIn()) {
                $address = $this->session->getCustomer()->getPrimaryBillingAddress() ?
                    $this->session->getCustomer()->getPrimaryBillingAddress() :
                    $this->session->getCustomer()->getDefaultShippingAddress();
                $country = $address->getCountryId();
            }

            if (in_array($country, PayLaterLink::ARRAY_PAYLATER_SUPPORTED_COUNTRIES)) {
                $this->buyerCountry = $country;
            } else {
                $this->buyerCountry = '';
            }
        }

        return $this->buyerCountry;
    }
}
