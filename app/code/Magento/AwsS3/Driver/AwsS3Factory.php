<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AwsS3\Driver;

use Aws\S3\S3Client;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\RemoteStorage\Driver\Adapter\Cache\CacheInterface;
use Magento\RemoteStorage\Driver\Adapter\CachedAdapter;
use Magento\RemoteStorage\Driver\Adapter\MetadataProviderFactoryInterface;
use Magento\RemoteStorage\Driver\DriverException;
use Magento\RemoteStorage\Driver\DriverFactoryInterface;
use Magento\RemoteStorage\Driver\RemoteDriverInterface;
use Magento\RemoteStorage\Model\Config;

/**
 * Creates a pre-configured instance of AWS S3 driver.
 */
class AwsS3Factory implements DriverFactoryInterface
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var MetadataProviderFactoryInterface
     */
    private $metadataProviderFactory;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param Config $config
     * @param MetadataProviderFactoryInterface $metadataProviderFactory
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        Config $config,
        MetadataProviderFactoryInterface $metadataProviderFactory
    ) {
        $this->objectManager = $objectManager;
        $this->config = $config;
        $this->metadataProviderFactory = $metadataProviderFactory;
    }

    /**
     * @inheritDoc
     */
    public function create(): RemoteDriverInterface
    {
        try {
            return $this->createConfigured(
                $this->config->getConfig(),
                $this->config->getPrefix()
            );
        } catch (LocalizedException $exception) {
            throw new DriverException(__($exception->getMessage()), $exception);
        }
    }

    /**
     * @inheritDoc
     */
    public function createConfigured(
        array $config,
        string $prefix,
        string $cacheAdapter = '',
        array $cacheConfig = []
    ): RemoteDriverInterface {
        $config['version'] = 'latest';

        if (empty($config['credentials']['key']) || empty($config['credentials']['secret'])) {
            unset($config['credentials']);
        }

        if (empty($config['bucket']) || empty($config['region'])) {
            throw new DriverException(__('Bucket and region are required values'));
        }

        if (!empty($config['http_handler'])) {
            $config['http_handler'] = $this->objectManager->create($config['http_handler'])($config);
        }

        $client = new S3Client($config);
        $adapter = new AwsS3V3Adapter($client, $config['bucket'], $prefix);
        $cache = $this->objectManager->get(CacheInterface::class);

        return $this->objectManager->create(
            AwsS3::class,
            [
                'adapter' => $this->objectManager->create(CachedAdapter::class, [
                    'adapter' => $adapter,
                    'cache' => $cache
                ]),
                'objectUrl' => $client->getObjectUrl($config['bucket'], trim($prefix, '\\/') . '/.'),
                'metadataProvider' => $this->metadataProviderFactory->create($adapter, $cache),
            ]
        );
    }
}
