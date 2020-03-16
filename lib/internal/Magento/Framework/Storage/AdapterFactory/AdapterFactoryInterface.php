<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Storage\AdapterFactory;

use League\Flysystem\AdapterInterface;

/**
 * Storage adapter factory
 *
 * A storage adapter should have a factory implementing this interface in order to be supported by Magento.
 * A new factory should be registered in \Magento\Framework\Storage\StorageProvider::$storageAdapters via di.xml.
 */
interface AdapterFactoryInterface
{
    /**
     * Create instance of a storage adapter
     *
     * @param array $options
     * @return AdapterInterface
     */
    public function create(array $options): AdapterInterface;
}
