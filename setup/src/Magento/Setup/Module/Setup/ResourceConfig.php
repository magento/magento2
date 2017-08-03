<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\Setup;

/**
 * Simplified resource config for Setup tools
 * @since 2.0.0
 */
class ResourceConfig implements \Magento\Framework\App\ResourceConnection\ConfigInterface
{
    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getConnectionName($resourceName)
    {
        return \Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION;
    }
}
