<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Payment\Helper\Data as PaymentHelper;

/**
 * Default implementation of credits card configuration provider.
 * Use this class to register payment method that supports credit cards.
 * Direct injection as a dependency or extending of this class is not recommended.
 *
 * @api
 */
class CcGenericConfigProvider implements ConfigProviderInterface
{
    /**
     * @var CcConfig
     */
    protected $ccConfig;

    /**
     * @var MethodInterface[]
     */
    protected $methods = [];

    /**
     * @param CcConfig $ccConfig
     * @param PaymentHelper $paymentHelper
     * @param array $methodCodes
     */
    public function __construct(
        CcConfig $ccConfig,
        PaymentHelper $paymentHelper,
        array $methodCodes = []
    ) {
        $this->ccConfig = $ccConfig;
        foreach ($methodCodes as $code) {
            $this->methods[$code] = $paymentHelper->getMethodInstance($code);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $config = [];
        foreach ($this->methods as $methodCode => $method) {
            if ($method->isAvailable()) {
                $config = array_merge_recursive($config, [
                    'payment' => [
                        'ccform' => [
                            'availableTypes' => [$methodCode => $this->getCcAvailableTypes($methodCode)],
                            'months' => [$methodCode => $this->getCcMonths()],
                            'years' => [$methodCode => $this->getCcYears()],
                            'hasVerification' => [$methodCode => $this->hasVerification($methodCode)],
                            'cvvImageUrl' => [$methodCode => $this->getCvvImageUrl()]
                        ]
                    ]
                ]);
            }
        }
        return $config;
    }

    /**
     * Solo/switch card start years
     *
     * @return array
     * @deprecated 2.1.0 unused
     */
    protected function getSsStartYears()
    {
        return $this->ccConfig->getSsStartYears();
    }

    /**
     * Retrieve credit card expire months
     *
     * @return array
     */
    protected function getCcMonths()
    {
        return $this->ccConfig->getCcMonths();
    }

    /**
     * Retrieve credit card expire years
     *
     * @return array
     */
    protected function getCcYears()
    {
        return $this->ccConfig->getCcYears();
    }

    /**
     * Retrieve CVV tooltip image url
     *
     * @return string
     */
    protected function getCvvImageUrl()
    {
        return $this->ccConfig->getCvvImageUrl();
    }

    /**
     * Retrieve availables credit card types
     *
     * @param string $methodCode
     * @return array
     */
    protected function getCcAvailableTypes($methodCode)
    {
        $types = $this->ccConfig->getCcAvailableTypes();
        $availableTypes = $this->methods[$methodCode]->getConfigData('cctypes');
        if ($availableTypes) {
            $availableTypes = explode(',', $availableTypes);
            foreach (array_keys($types) as $code) {
                if (!in_array($code, $availableTypes)) {
                    unset($types[$code]);
                }
            }
        }
        return $types;
    }

    /**
     * Retrieve has verification configuration
     *
     * @param string $methodCode
     * @return bool
     */
    protected function hasVerification($methodCode)
    {
        $result = $this->ccConfig->hasVerification();
        $configData = $this->methods[$methodCode]->getConfigData('useccv');
        if ($configData !== null) {
            $result = (bool)$configData;
        }
        return $result;
    }

    /**
     * Whether switch/solo card type available
     *
     * @param string $methodCode
     * @return bool
     * @deprecated 2.1.0 unused
     */
    protected function hasSsCardType($methodCode)
    {
        $result = false;
        $availableTypes = explode(',', $this->methods[$methodCode]->getConfigData('cctypes'));
        $ssPresentations = array_intersect(['SS', 'SM', 'SO'], $availableTypes);
        if ($availableTypes && count($ssPresentations) > 0) {
            $result = true;
        }
        return $result;
    }
}
