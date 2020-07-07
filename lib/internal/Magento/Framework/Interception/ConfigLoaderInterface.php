<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Interception;

use Magento\Framework\Exception\FileSystemException;

/**
 * Interception configuration loader interface.
 */
interface ConfigLoaderInterface
{
    /**
     * Load interception configuration data per scope.
     *
     * @param string $cacheId
     * @return array
     * @throws FileSystemException
     */
    public function load(string $cacheId): array;
}
