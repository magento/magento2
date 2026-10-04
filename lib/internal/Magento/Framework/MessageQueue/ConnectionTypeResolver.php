<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\MessageQueue;

/**
 * {@inheritdoc}
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
     * {@inheritdoc}
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
