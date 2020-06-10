<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Interception;

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
     */
    public function load($cacheId);
}
