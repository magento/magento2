<?php
/**
 * Resource configuration interface
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Resource;

interface ConfigInterface
{
    /**
     * Retrieve resource connection instance name
     *
     * @param string $resourceName
     * @return string
     */
    public function getConnectionName($resourceName);
}
