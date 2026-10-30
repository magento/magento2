<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\RemoteStorage\Driver\Adapter;

use League\Flysystem\UnableToRetrieveMetadata;

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
     * @throws UnableToRetrieveMetadata
     */
    public function getMetadata(string $path): array;
}
