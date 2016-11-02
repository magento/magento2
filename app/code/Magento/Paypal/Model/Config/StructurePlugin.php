<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model\Config;

use Magento\Config\Model\Config\ScopeDefiner;
use Magento\Config\Model\Config\Structure;
use Magento\Config\Model\Config\Structure\Element\Section;
use Magento\Config\Model\Config\Structure\ElementInterface;
use Magento\Paypal\Helper\Backend as BackendHelper;

class StructurePlugin
{
    /**
     * Request parameter name
     */
    const REQUEST_PARAM_COUNTRY = 'paypal_country';

    /**
     * @var BackendHelper
     */
    protected $_helper;

    /**
     * @var ScopeDefiner
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
     * @param ScopeDefiner $scopeDefiner
     * @param BackendHelper $helper
     */
    public function __construct(
        ScopeDefiner $scopeDefiner,
        BackendHelper $helper
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
     * @param Structure $subject
     * @param \Closure $proceed
     * @param array $pathParts
     * @return ElementInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetElementByPathParts(
        Structure $subject,
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
        /** @var ElementInterface $result */
        $result = $proceed($pathParts);
        if ($isSectionChanged && isset($result)) {
            if ($result instanceof Section) {
                $this->restructurePayments($result);
                $result->setData(array_merge(
                    $result->getData(),
                    ['showInDefault' => true, 'showInWebsite' => true, 'showInStore' => true]
                ), $this->_scopeDefiner->getScope());
            }
        }
        return $result;
    }

    /**
     * Changes payment config structure.
     * Groups which have `displayIn` element, transfer to appropriate group.
     * Groups without `displayIn` transfer to other payment methods group.
     *
     * @param Section $result
     * @return void
     */
    private function restructurePayments(Section $result)
    {
        $sectionMap = [
            'account' => [],
            'recommended_solutions' => [],
            'other_paypal_payment_solutions' => [],
            'other_payment_methods' => []
        ];

        $configuration = $result->getData();

        foreach ($configuration['children'] as $section => $data) {
            if (array_key_exists($section, $sectionMap)) {
                $sectionMap[$section] = $data;
            } elseif ($displayIn = $this->getDisplayInSection($section, $data)) {
                $sectionMap[$displayIn['parent']]['children'][$displayIn['section']] = $displayIn['data'];
            } else {
                $sectionMap['other_payment_methods']['children'][$section] = $data;
            }
        }

        $configuration['children'] = $sectionMap;
        $result->setData($configuration, $this->_scopeDefiner->getScope());
    }

    /**
     * Recursive search of `displayIn` element in node children
     *
     * @param string $section
     * @param array $data
     * @return array|null
     */
    private function getDisplayInSection($section, $data)
    {
        if (is_array($data) && array_key_exists('displayIn', $data)) {
            return [
                'parent' => $data['displayIn'],
                'section' => $section,
                'data' => $data
            ];
        }

        if (array_key_exists('children', $data)) {
            foreach ($data['children'] as $childSection => $childData) {
                return $this->getDisplayInSection($childSection, $childData);
            }
        }

        return null;
    }
}
