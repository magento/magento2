<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue;

/**
 * Message Queue connection type resolver.
 * @since 2.2.0
 */
interface ConnectionTypeResolverInterface
{
    /**
     * Get connection type by connection name.
     *
     * @param string $connectionName
     * @return string|null
     * @since 2.2.0
     */
    public function getConnectionType($connectionName);
}
