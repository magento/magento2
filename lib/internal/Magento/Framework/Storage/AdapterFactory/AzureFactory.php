<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Storage\AdapterFactory;

use League\Flysystem\AdapterInterface;
use League\Flysystem\AzureBlobStorage\AzureBlobStorageAdapter;
use Magento\Framework\Storage\InvalidStorageConfigurationException;
use MicrosoftAzure\Storage\Blob\BlobRestProxy;

/**
 * Factory for Azure storage adapter
 */
class AzureFactory implements AdapterFactoryInterface
{
    /**
     * @inheritdoc
     */
    public function create(array $options): AdapterInterface
    {
        if (empty($options['connection_string']) || empty($options['container_name'])) {
            throw new InvalidStorageConfigurationException(
                "Can't create Azure Blob storage adapter: " .
                "required 'connection_string' and/or 'container_name' options are absent"
            );
        }
        $client = BlobRestProxy::createBlobService($options['connection_string']);
        return new AzureBlobStorageAdapter($client, $options['container_name'], $options['prefix'] ?? null);
    }
}
