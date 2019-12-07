<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\NewRelicReporting\Model;

/**
 * Wrapper for New Relic functions
 *
 * @codeCoverageIgnore
 */
class NewRelicWrapper
{
    /**
     * Wrapper for 'newrelic_add_custom_parameter' function
     *
     * @param string $param
     * @param string|int $value
     * @return bool
     */
    public function addCustomParameter($param, $value)
    {
        if ($this->isExtensionInstalled()) {
            newrelic_add_custom_parameter($param, $value);
            return true;
        }
        return false;
    }

    /**
     * Wrapper for 'newrelic_notice_error' function
     *
     * @param  \Exception $exception
     * @return void
     */
    public function reportError($exception)
    {
        if ($this->isExtensionInstalled()) {
            newrelic_notice_error($exception->getMessage(), $exception);
        }
    }

    /**
     * Wrapper for 'newrelic_set_appname'
     *
     * @param string $appName
     * @return void
     */
    public function setAppName(string $appName)
    {
        if ($this->isExtensionInstalled()) {
            newrelic_set_appname($appName);
        }
    }

    /**
     * Wrapper for 'newrelic_name_transaction'
     *
     * @param string $transactionName
     * @return void
     */
    public function setTransactionName(string $transactionName): void
    {
        if ($this->isExtensionInstalled()) {
            newrelic_name_transaction($transactionName);
        }
    }

    /**
     * Checks whether newrelic-php5 agent is installed
     *
     * @return bool
     */
    public function isExtensionInstalled()
    {
        if (extension_loaded('newrelic')) {
            return true;
        }
        return false;
    }
}
