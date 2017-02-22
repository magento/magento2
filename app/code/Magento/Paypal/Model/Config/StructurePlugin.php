<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model\Config;

class StructurePlugin
{
    /**
     * Request parameter name
     */
    const REQUEST_PARAM_COUNTRY = 'paypal_country';

    /**
     * @var \Magento\Paypal\Helper\Backend
     */
    protected $_helper;

    /**
     * @var \Magento\Config\Model\Config\ScopeDefiner
     */
    protected $_scopeDefiner;

    /**
     * @var string[]
     */
    private static $_paypalConfigCountries = [
        'payment_us',
        'payment_ca',
        'payment_au',
        'payment_gb',
        'payment_jp',
        'payment_fr',
        'payment_it',
        'payment_es',
        'payment_hk',
        'payment_nz',
        'payment_de',
    ];

    /**
     * @param \Magento\Config\Model\Config\ScopeDefiner $scopeDefiner
     * @param \Magento\Paypal\Helper\Backend $helper
     */
    public function __construct(
        \Magento\Config\Model\Config\ScopeDefiner $scopeDefiner,
        \Magento\Paypal\Helper\Backend $helper
    ) {
        $this->_scopeDefiner = $scopeDefiner;
        $this->_helper = $helper;
    }

    /**
     * Get paypal configuration countries
     *
     * @param bool $addOther
     * @return string[]
     */
    public static function getPaypalConfigCountries($addOther = false)
    {
        $countries = self::$_paypalConfigCountries;
        if ($addOther) {
            $countries[] = 'payment_other';
        }
        return $countries;
    }

    /**
     * Substitute payment section with PayPal configs
     *
     * @param \Magento\Config\Model\Config\Structure $subject
     * @param \Closure $proceed
     * @param array $pathParts
     * @return \Magento\Config\Model\Config\Structure\ElementInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetElementByPathParts(
        \Magento\Config\Model\Config\Structure $subject,
        \Closure $proceed,
        array $pathParts
    ) {
        $isSectionChanged = $pathParts[0] == 'payment';
        if ($isSectionChanged) {
            $requestedCountrySection = 'payment_' . strtolower($this->_helper->getConfigurationCountryCode());
            if (in_array($requestedCountrySection, self::getPaypalConfigCountries())) {
                $pathParts[0] = $requestedCountrySection;
            } else {
                $pathParts[0] = 'payment_other';
            }
        }
        /** @var \Magento\Config\Model\Config\Structure\ElementInterface $result */
        $result = $proceed($pathParts);
        if ($isSectionChanged && isset($result)) {
            if ($result instanceof \Magento\Config\Model\Config\Structure\Element\Section) {
                $result->setData(array_merge(
                    $result->getData(),
                    ['showInDefault' => true, 'showInWebsite' => true, 'showInStore' => true]
                ), $this->_scopeDefiner->getScope());
            }
        }
        return $result;
    }
}
