<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\MessageQueue\Consumer\Config\Env;

/**
 * Communication configuration reader. Reads data from env.php.
 * @since 2.2.0
 */
class Reader implements \Magento\Framework\Config\ReaderInterface
{
    /**
     * @var \Magento\Framework\MessageQueue\Config\Reader\Env
     * @since 2.2.0
     */
    private $envConfig;

    /**
     * @param \Magento\Framework\MessageQueue\Config\Reader\Env $envConfig
     * @since 2.2.0
     */
    public function __construct(\Magento\Framework\MessageQueue\Config\Reader\Env $envConfig)
    {
        $this->envConfig = $envConfig;
    }

    /**
     * Read consumers configuration from env.php
     *
     * @param string|null $scope
     * @return array
     * @since 2.2.0
     */
    public function read($scope = null)
    {
        $configData = $this->envConfig->read($scope);
        return isset($configData[\Magento\Framework\MessageQueue\Config\Reader\Env::ENV_CONSUMERS])
            ? $configData[\Magento\Framework\MessageQueue\Config\Reader\Env::ENV_CONSUMERS]
            : [];
    }
}
