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
     * @TODO Remove this in 2.4-dev branch
     * @var bool
     */
    private $transactionOpen = false;

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
        if ($this->transactionOpen === true) {
            $this->endTransaction();
        }

        if ($this->isExtensionInstalled()) {
            newrelic_set_appname($appName);

            // Remove following line in 2.4-dev branch
            $this->transactionOpen = true;
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
     * Cannot be public in 2.3-dev due to Backward Compatibility
     *
     * @see https://devdocs.magento.com/guides/v2.3/contributor-guide/backward-compatible-development/
     * @TODO Make it public in 2.4-dev branch.
     *
     * @param bool $ignore
     * @return void
     */
    private function endTransaction($ignore = false)
    {
        if ($this->isExtensionInstalled()) {
            newrelic_end_transaction($ignore);

            // @TODO Remove following line in 2.4-dev branch
            $this->transactionOpen = false;
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
