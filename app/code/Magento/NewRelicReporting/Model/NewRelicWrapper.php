<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
        if (extension_loaded('newrelic')) {
            newrelic_add_custom_parameter($param, $value);
            return true;
        }
        return false;
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
