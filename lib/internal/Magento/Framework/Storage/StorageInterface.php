<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Storage;

use League\Flysystem\FilesystemInterface;

/**
 * Storage interface to be used by client code to manipulate objects in the storage
 *
 * Retrieve a real instance of storage via $storageProvider->get('<your-storage-name>'),
 * where $storageProvider is an instance of \Magento\Framework\Storage\StorageProvider
 */
interface StorageInterface extends FilesystemInterface
{

}
