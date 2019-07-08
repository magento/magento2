<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\MessageQueue\Consumer\Config\Env;

/**
 * Communication configuration reader. Reads data from env.php.
 */
class Reader implements \Magento\Framework\Config\ReaderInterface
{
    /**
     * @var \Magento\Framework\MessageQueue\Config\Reader\Env
     */
    private $envConfig;

    /**
     * @param \Magento\Framework\MessageQueue\Config\Reader\Env $envConfig
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
     */
    public function read($scope = null)
    {
        $configData = $this->envConfig->read($scope);
        return $configData[\Magento\Framework\MessageQueue\Config\Reader\Env::ENV_CONSUMERS] ?? [];
    }
}
