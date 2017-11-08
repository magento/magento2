<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQl\Model;

use Magento\Framework\ObjectManagerInterface;

/**
 * Create resolver class to generate resolve function for GraphQL type
 */
class ResolverFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Instantiate resolver class from class name
     *
     * @param string $resolverName
     * @return ResolverInterface
     */
    public function create($resolverName)
    {
        $qualifiedName = __NAMESPACE__ . '\\Resolver\\' . $resolverName;
        if (!class_exists($qualifiedName)) {
            throw new \LogicException(sprintf('Resolver %s does not exist', $resolverName));
        }
        $resolver = $this->objectManager->create($qualifiedName);
        if (!$resolver instanceof ResolverInterface) {
            throw new \LogicException(sprintf('Class name %s is not a resolver', $resolverName));
        }

        return $resolver;
    }
}
