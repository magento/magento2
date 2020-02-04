<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue;

/**
 * Verify resolvers and return connection type
 */
class ConnectionTypeResolver
{
    /**
     * @var ConnectionTypeResolverInterface[]
     */
    private $resolvers;

    /**
     * Initialize dependencies.
     *
     * @param ConnectionTypeResolverInterface[] $resolvers
     */
    public function __construct($resolvers = [])
    {
        $this->resolvers = $resolvers;
    }

    /**
     * Get connection type.
     *
     * @param string $connectionName
     * @return string|null
     */
    public function getConnectionType($connectionName)
    {
        $type = null;

        foreach ($this->resolvers as $resolver) {
            $type = $resolver->getConnectionType($connectionName);
            if ($type != null) {
                break;
            }
        }

        if ($type === null) {
            throw new \LogicException('Unknown connection name ' . $connectionName);
        }
        return $type;
    }
}
