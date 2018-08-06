<?php
/**
 * Resource configuration interface
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\ResourceConnection;

/**
 * Interface \Magento\Framework\App\ResourceConnection\ConfigInterface
 *
 */
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
