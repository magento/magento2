<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Config\Reader;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\MessageQueue\Config\Reader\Env\Converter\Publisher as PublisherConverter;
use Magento\Framework\App\ObjectManager;

/**
 * Communication configuration reader. Reads data from env.php.
 */
class Env implements \Magento\Framework\Config\ReaderInterface
{
    const ENV_QUEUE  = 'queue';
    const ENV_PUBLISHERS  = 'publishers';
    const ENV_TOPICS = 'topics';
    const ENV_CONSUMERS = 'consumers';
    const ENV_CONSUMER_CONNECTION = 'connection';
    const ENV_CONSUMER_MAX_MESSAGES = 'max_messages';

    /**
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @var PublisherConverter
     */
    private $publisherConverter;

    /**
     * @param DeploymentConfig $deploymentConfig
     * @param PublisherConverter|null $publisherConverter
     */
    public function __construct(
        DeploymentConfig $deploymentConfig,
        PublisherConverter $publisherConverter = null
    ) {
        $this->deploymentConfig = $deploymentConfig;
        $this->publisherConverter = $publisherConverter ?: ObjectManager::getInstance()->get(PublisherConverter::class);
    }

    /**
     * Read communication configuration from env.php
     *
     * @param string|null $scope
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function read($scope = null)
    {
        $configData = $this->deploymentConfig->getConfigData(self::ENV_QUEUE) ?: [];
        if (isset($configData['config'])) {
            $configData = $this->publisherConverter->convert($configData = $configData['config']);
        }
        return $configData;
    }
}
