<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * \Directory currency abstract backend model
 *
 * Allows dispatching before and after events for each controller action
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Config\Model\Config\Backend\Currency;

/**
 * Base currency class
 *
 * @api
 * @since 100.0.2
 */
abstract class AbstractCurrency extends \Magento\Framework\App\Config\Value
{
    /**
     * Retrieve allowed currencies for current scope
     *
     * @return array
     */
    protected function _getAllowedCurrencies()
    {
        $allowValue = $this->getData('groups/options/fields/allow/value');
        $allowedCurrencies = $allowValue === null || $this->getData('groups/options/fields/allow/inherit')
            ? explode(
                ',',
                (string)$this->_config->getValue(
                    \Magento\Directory\Model\Currency::XML_PATH_CURRENCY_ALLOW,
                    $this->getScope(),
                    $this->getScopeId()
                )
            )
            : (array) $allowValue;

        return $allowedCurrencies;
    }

    /**
     * Retrieve Installed Currencies
     *
     * @return string[]
     */
    protected function _getInstalledCurrencies()
    {
        return explode(
            ',',
            $this->_config->getValue(
                'system/currency/installed',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
        );
    }

    /**
     * Retrieve Base Currency value for current scope
     *
     * @return string
     */
    protected function _getCurrencyBase()
    {
        $value = $this->getData('groups/options/fields/base/value');
        if (!$this->isFormData() || !$value) {
            $value = $this->_config->getValue(
                \Magento\Directory\Model\Currency::XML_PATH_CURRENCY_BASE,
                $this->getScope(),
                $this->getScopeId()
            );
        }
        return (string)$value;
    }

    /**
     * Retrieve Default display Currency value for current scope
     *
     * @return string
     */
    protected function _getCurrencyDefault()
    {
        if (!$this->isFormData() || !($value = $this->getData('groups/options/fields/default/value'))) {
            $value = $this->_config->getValue(
                \Magento\Directory\Model\Currency::XML_PATH_CURRENCY_DEFAULT,
                $this->getScope(),
                $this->getScopeId()
            );
        }
        return (string)$value;
    }

    /**
     * Check whether field saved from Admin form with other currency data or as single field, e.g. from CLI command
     *
     * @return bool True in case when field was saved from Admin form
     */
    private function isFormData()
    {
        return $this->getData('groups/options/fields') !== null;
    }
}
