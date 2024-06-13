<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\App\ResourceConnection;

/**
 * Interface \Magento\Framework\App\ResourceConnection\ConfigInterface
 *
 * @api
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
