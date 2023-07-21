<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\NewRelicReporting\Model;

use Throwable;

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
     * @param Throwable $exception
     * @return void
     */
    public function reportError(Throwable $exception)
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
     * Wrapper for 'newrelic_end_transaction'
     *
     * @param bool $ignore
     * @return void
     */
    public function endTransaction($ignore = false)
    {
        if ($this->isExtensionInstalled()) {
            newrelic_end_transaction($ignore);
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
