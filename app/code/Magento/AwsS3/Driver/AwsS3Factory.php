<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AwsS3\Driver;

use Aws\S3\S3Client;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use Magento\Framework\ObjectManagerInterface;
use Magento\RemoteStorage\Driver\DriverException;
use Magento\RemoteStorage\Driver\DriverFactoryInterface;
use Magento\RemoteStorage\Driver\RemoteDriverInterface;

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
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @inheritDoc
     */
    public function create(array $config, string $prefix): RemoteDriverInterface
    {
        $config['version'] = 'latest';

        if (empty($config['credentials']['key']) || empty($config['credentials']['secret'])) {
            unset($config['credentials']);
        }

        if (empty($config['bucket']) || empty($config['region'])) {
            throw new DriverException(__('Bucket and region are required values'));
        }

        $client = new S3Client($config);
        $adapter = new AwsS3Adapter($client, $config['bucket'], $prefix);

        return $this->objectManager->create(
            AwsS3::class,
            [
                'adapter' => $adapter,
                'objectUrl' => $client->getObjectUrl($adapter->getBucket(), $adapter->applyPathPrefix('.'))
            ]
        );
    }
}
