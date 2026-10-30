<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\RemoteStorage\Driver\Adapter;

use League\Flysystem\FilesystemAdapter;

/**
 * Cached adapter interface for filesystem storage.
 */
interface CachedAdapterInterface extends FilesystemAdapter
{

}
