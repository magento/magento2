<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model;

use Magento\Payment\Model\Method\AbstractMethod;

/**
 * Class PayflowConfig
 * @todo ELiminate current configuration class
 * @since 2.0.0
 */
class PayflowConfig extends Config
{
    /**#@-*/

    /**#@+
     * Payment transaction types
     */
    const TRXTYPE_AUTH_ONLY = 'A';

    const TRXTYPE_SALE = 'S';

    /**#@-*/

    /**
     * Mapper from Magento payment actions to PayPal-specific transaction types
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getTrxType()
    {
        switch ($this->getValue('payment_action')) {
            case self::PAYMENT_ACTION_AUTH:
                return self::TRXTYPE_AUTH_ONLY;
            case self::PAYMENT_ACTION_SALE:
                return self::TRXTYPE_SALE;
            default:
                break;
        }

        return null;
    }

    /**
     * Getter for URL to perform Payflow requests, based on test mode by default
     *
     * @param bool|null $testMode Ability to specify test mode using
     * @return string
     * @since 2.0.0
     */
    public function getTransactionUrl($testMode = null)
    {
        $testMode = $testMode === null ? $this->getValue('sandbox_flag') : (bool)$testMode;
        if ($testMode) {
            return $this->methodInstance->getConfigData('transaction_url_test_mode');
        }
        return $this->methodInstance->getConfigData('transaction_url');
    }

    /**
     * Payment action getter compatible with payment model
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getPaymentAction()
    {
        switch ($this->getValue('payment_action')) {
            case self::PAYMENT_ACTION_AUTH:
                return AbstractMethod::ACTION_AUTHORIZE;
            case self::PAYMENT_ACTION_SALE:
                return AbstractMethod::ACTION_AUTHORIZE_CAPTURE;
            default:
                break;
        }
        return null;
    }

    /**
     * Check whether method active in configuration and supported for merchant country or not
     *
     * @param string $method Method code
     * @return bool
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function isMethodActive($method)
    {
        return parent::isMethodActive(Config::METHOD_PAYMENT_PRO)
            || parent::isMethodActive(Config::METHOD_PAYFLOWPRO);
    }

    /**
     * Map any supported payment method into a config path by specified field name
     *
     * @param string $fieldName
     * @return string|null
     * @since 2.0.0
     */
    protected function _getSpecificConfigPath($fieldName)
    {
        if ($this->pathPattern) {
            return sprintf($this->pathPattern, $this->_methodCode, $fieldName);
        }

        return "payment/{$this->_methodCode}/{$fieldName}";
    }
}
