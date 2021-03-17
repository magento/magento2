<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\RemoteStorage\Model\Storage\Handler;

/**
 * Represents cache storage model.
 */
interface CacheStorageHandlerInterface
{
    /**
     * Store the cache.
     *
     * @return void
     * @throws \League\Flysystem\FilesystemException
     */
    public function save(): void;

    /**
     * Load the cache.
     *
     * @return void
     * @throws \League\Flysystem\FilesystemException
     */
    public function load(): void;
}
