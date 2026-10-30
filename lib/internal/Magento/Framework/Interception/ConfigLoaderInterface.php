<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Interception;

/**
 * Interception configuration loader interface.
 *
 * @api
 */
interface ConfigLoaderInterface
{
    /**
     * Load interception configuration data per scope.
     *
     * @param string $cacheId
     * @return array
     */
    public function load(string $cacheId): array;
}
