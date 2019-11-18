<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue;

/**
 * @inheritdoc
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
    public function __construct($resolvers)
    {
        $this->resolvers = $resolvers;
    }

    /**
     * Get connection type based on connection name
     *
     * @param string $connectionName
     * @return string|null
     */
    public function getConnectionType($connectionName)
    {
        $type = null;

        if (is_array($this->resolvers)) {
            foreach ($this->resolvers as $resolver) {
                $type = $resolver->getConnectionType($connectionName);
                if ($type != null) {
                    break;
                }
            }
        }

        if ($type === null) {
            throw new \LogicException('Unknown connection name ' . $connectionName);
        }

        return $type;
    }
}
