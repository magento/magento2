<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Query\Resolver;

use Magento\Framework\GraphQl\Query\BatchContractResolverWrapper;
use Magento\Framework\GraphQl\Query\BatchResolverWrapper;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\ObjectManagerInterface;

/**
 * Creates GraphQL resolvers based on configurations.
 */
class Factory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Factory constructor.
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create resolver by class name.
     *
     * @param string $class
     * @return ResolverInterface
     */
    public function createByClass(string $class): ResolverInterface
    {
        $resolverInstance = $this->objectManager->get($class);
        if ($resolverInstance instanceof BatchResolverInterface) {
            $resolver = $this->objectManager->create(BatchResolverWrapper::class, ['resolver' => $resolverInstance]);
        } elseif ($resolverInstance instanceof BatchServiceContractResolverInterface) {
            $resolver = $this->objectManager->create(
                BatchContractResolverWrapper::class,
                ['resolver' => $resolverInstance]
            );
        } elseif ($resolverInstance instanceof ResolverInterface) {
            $resolver = $resolverInstance;
        } else {
            throw new \RuntimeException($class .' cannot function as a GraphQL resolver');
        }

        return $resolver;
    }
}
