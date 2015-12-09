<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Config\Reader;

use Magento\Framework\App\DeploymentConfig;

/**
 * Communication configuration reader. Reads data from env.php.
 */
class EnvReader implements \Magento\Framework\Config\ReaderInterface
{
    const ENV_QUEUE  = 'queue';
    const ENV_TOPICS = 'topics';
    const ENV_CONSUMERS = 'consumers';
    const ENV_CONSUMER_CONNECTION = 'connection';
    const ENV_CONSUMER_MAX_MESSAGES = 'max_messages';

    /**
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @param DeploymentConfig $deploymentConfig
     */
    public function __construct(
        DeploymentConfig $deploymentConfig
    ) {
        $this->deploymentConfig = $deploymentConfig;
    }

    /**
     * Read communication configuration from env.php
     *
     * @param string|null $scope
     * @return array
     */
    public function read($scope = null)
    {
        $configData = $this->deploymentConfig->getConfigData(self::ENV_QUEUE);
        return $configData ?: [];
    }
}
