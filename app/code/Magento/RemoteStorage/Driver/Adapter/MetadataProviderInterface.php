<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\RemoteStorage\Driver\Adapter;

/**
 * Interface for metadata provider. Provides metadata of the file by given path.
 */
interface MetadataProviderInterface
{
    /**
     * Retrieve metadata for a file by path.
     *
     * @param string $path
     * @return array
     */
    public function getMetadata(string $path): array;
}
