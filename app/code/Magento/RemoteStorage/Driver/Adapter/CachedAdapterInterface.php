<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
