<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Mtf\Util\Command\Cli;

use Magento\Mtf\Util\Command\Cli;

/**
 * Handle set configuration for test execution.
 */
class Config extends Cli
{
    /**
     * Parameter for reindex command.
     */
    const PARAM_CONFIG_SET = 'config:set';

    /**
     * Set configuration.
     *
     * @param string $path
     * @param string $value
     * @param string|null $scope
     * @param string|null $scopeCode
     * @return void
     */
    public function setConfig($path, $value, $scope = null, $scopeCode = null)
    {
        $configurationString = '';

        if ($scope !== null) {
            $configurationString.= sprintf('--scope=%s ', $scope);
        }

        if ($scopeCode !== null) {
            $configurationString.= sprintf('--scope-code=%s ', $scopeCode);
        }
        $configurationString.= sprintf('%s %s', $path, $value);

        parent::execute(Config::PARAM_CONFIG_SET . ' ' . $configurationString);
    }
}
