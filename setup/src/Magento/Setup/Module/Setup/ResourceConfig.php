<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\Setup;

/**
 * Simplified resource config for Setup tools
 */
class ResourceConfig implements \Magento\Framework\App\ResourceConnection\ConfigInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConnectionName($resourceName)
    {
        return \Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION;
    }
}
