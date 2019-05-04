<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\App\Mode;

/**
 * This class is responsible for providing configuration while switching application mode
 */
class ConfigProvider
{
    /**
     * Configuration for combinations of $currentMode and $targetMode
     * For example:
     * [
     *      'developer' => [
     *          'production' => [
     *              {{setting_path}} => {{setting_value}}
     *          ]
     *      ]
     * ]
     *
     * @var array
     */
    private $config;

    /**
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * Provide configuration while switching from $currentMode to $targetMode
     * This method used in \Magento\Deploy\Model\Mode::setStoreMode
     *
     * For example: while switching from developer mode to production mode
     * need to turn off 'dev/debug/debug_logging' setting in this case method
     * will return array
     * [
     *      {{setting_path}} => {{setting_value}}
     * ]
     *
     * @param string $currentMode
     * @param string $targetMode
     * @return array
     */
    public function getConfigs($currentMode, $targetMode)
    {
        if (isset($this->config[$currentMode][$targetMode])) {
            return $this->config[$currentMode][$targetMode];
        }
        return [];
    }
}
