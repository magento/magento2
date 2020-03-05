<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Storage\AdapterFactory;

use League\Flysystem\Adapter\Local;
use League\Flysystem\AdapterInterface;
use Magento\Framework\Storage\InvalidStorageConfigurationException;

/**
 * Factory for local filesystem storage adapter
 */
class LocalFactory implements AdapterFactoryInterface
{
    public const ADAPTER_NAME = 'local';

    /**
     * @inheritdoc
     */
    public function create(array $options): AdapterInterface
    {
        if (empty($options['root'])) {
            throw new InvalidStorageConfigurationException(
                "Can't create local filesystem storage adapter: required 'root' option is absent"
            );
        }
        return new Local($options['root']);
    }
}
