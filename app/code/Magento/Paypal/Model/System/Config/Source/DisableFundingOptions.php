<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Model\System\Config\Source;

use Magento\Paypal\Model\Config\StructurePlugin;

/**
 * Get disable funding options
 */
class DisableFundingOptions implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var \Magento\Paypal\Model\ConfigFactory
     */
    protected $_configFactory;

    protected $requestCountry;

    protected $http;

    /**
     * DisableFundingOptions constructor.
     * @param \Magento\Paypal\Model\ConfigFactory $configFactory
     * @param \Magento\Framework\App\Request\Http $http
     */
    public function __construct(
        \Magento\Paypal\Model\ConfigFactory $configFactory,
        \Magento\Framework\App\Request\Http $http
    ) {
        $this->_configFactory = $configFactory;
        $this->http = $http;
    }

    /**
     * @inheritdoc
     */
    public function toOptionArray(): array
    {
        $defaultFundingOptions = [
            [
                'value' => 'CARD',
                'label' => __('PayPal Guest Checkout Credit Card Icons')
            ],
            [
                'value' => 'ELV',
                'label' => __('Elektronisches Lastschriftverfahren - German ELV')
            ]
        ];
        $fundingOptions = $this->addPaypalCreditForUS($defaultFundingOptions);
        return $fundingOptions;
    }

    /**
     * Adds Paypal Credit for US
     *
     * @param {array} $fundingOptions
     * @return array
     */
    private function addPaypalCreditForUS($fundingOptions): array
    {
        $paypalCredit = [
            'value' => 'CREDIT',
            'label' => __('PayPal Credit')
        ];
        if ($this->checkMerchantCountry('US')) {
            array_unshift($fundingOptions, $paypalCredit);
        }
        return $fundingOptions;
    }

    /**
     * Checks for chosen Merchant country from the config/url
     *
     * @param {string} $country
     * @return bool
     */
    private function checkMerchantCountry($country): bool
    {
        $paypalCountry = $this->http->get(StructurePlugin::REQUEST_PARAM_COUNTRY);
        if ($paypalCountry) {
            return $paypalCountry === $country;
        }
        return $this->_configFactory->create()->getMerchantCountry() === $country;
    }
}
