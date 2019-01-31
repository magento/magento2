<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue;

/**
 * Message Queue connection type resolver.
 */
interface ConnectionTypeResolverInterface
{
    /**
     * Get connection type by connection name.
     *
     * @param string $connectionName
     * @return string|null
     */
    public function getConnectionType($connectionName);
}
