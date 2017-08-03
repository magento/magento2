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
 * @since 2.1.0
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
     * @since 2.1.0
     */
    private $deploymentConfig;

    /**
     * @var PublisherConverter
     * @since 2.2.0
     */
    private $publisherConverter;

    /**
     * @param DeploymentConfig $deploymentConfig
     * @param PublisherConverter|null $publisherConverter
     * @since 2.1.0
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
     * @since 2.1.0
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
