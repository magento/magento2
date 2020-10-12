<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AwsS3\Driver;

use Aws\S3\S3Client;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\RemoteStorage\Driver\DriverFactoryInterface;

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
     * Creates an instance of AWS S3 driver.
     *
     * @param array $config
     * @param string $prefix
     * @return DriverInterface
     */
    public function create(array $config, string $prefix): DriverInterface
    {
        $config += [
            'version' => 'latest'
        ];

        return $this->objectManager->create(
            AwsS3::class,
            [
                'adapter' => $this->objectManager->create(
                    AwsS3Adapter::class,
                    [
                        'client' => $this->objectManager->create(S3Client::class, ['args' => $config]),
                        'bucket' => $config['bucket'],
                        'prefix' => $prefix
                    ]
                )
            ]
        );
    }
}
