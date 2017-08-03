<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue;

/**
 * {@inheritdoc}
 * @since 2.2.0
 */
class ConnectionTypeResolver
{
    /**
     * @var ConnectionTypeResolverInterface[]
     * @since 2.2.0
     */
    private $resolvers;

    /**
     * Initialize dependencies.
     *
     * @param ConnectionTypeResolverInterface[] $resolvers
     * @since 2.2.0
     */
    public function __construct($resolvers)
    {
        $this->resolvers = $resolvers;
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
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
