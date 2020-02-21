<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Storage\AdapterFactory;

use Aws\S3\S3Client;
use League\Flysystem\AdapterInterface;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use Magento\Framework\Storage\InvalidStorageConfigurationException;

/**
 * Factory for AWS S3 storage adapter
 */
class AwsS3Factory implements AdapterFactoryInterface
{
    /**
     * @inheritdoc
     */
    public function create(array $options): AdapterInterface
    {
        if (empty($options['client']) || empty($options['bucket'])) {
            throw new InvalidStorageConfigurationException(
                "Can't create AWS S3 adapter: required 'client' and/or 'bucket' options are absent"
            );
        }
        $client = new S3Client($options['client']);
        return new AwsS3Adapter($client, $options['bucket'], $options['prefix'] ?? '');
    }
}
