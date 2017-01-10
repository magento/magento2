<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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

abstract class AbstractCurrency extends \Magento\Framework\App\Config\Value
{
    /**
     * Retrieve allowed currencies for current scope
     *
     * @return array
     */
    protected function _getAllowedCurrencies()
    {
        if ($this->getData('groups/options/fields/allow/inherit')) {
            return explode(
                ',',
                (string)$this->_config->getValue(
                    \Magento\Directory\Model\Currency::XML_PATH_CURRENCY_ALLOW,
                    $this->getScope(),
                    $this->getScopeId()
                )
            );
        }
        return $this->getData('groups/options/fields/allow/value');
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
        if (!($value = $this->getData('groups/options/fields/base/value'))) {
            $value = $this->_config->getValue(
                \Magento\Directory\Model\Currency::XML_PATH_CURRENCY_BASE,
                $this->getScope(),
                $this->getScopeId()
            );
        }
        return strval($value);
    }

    /**
     * Retrieve Default display Currency value for current scope
     *
     * @return string
     */
    protected function _getCurrencyDefault()
    {
        if (!($value = $this->getData('groups/options/fields/default/value'))) {
            $value = $this->_config->getValue(
                \Magento\Directory\Model\Currency::XML_PATH_CURRENCY_DEFAULT,
                $this->getScope(),
                $this->getScopeId()
            );
        }
        return strval($value);
    }
}
