<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQl\Model;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\ObjectManagerInterface;

/**
 * Create configured resolver class to resolve requested field data.
 */
class ResolverFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var QueryConfig
     */
    private $config;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param QueryConfig $config
     */
    public function __construct(ObjectManagerInterface $objectManager, QueryConfig $config)
    {
        $this->objectManager = $objectManager;
        $this->config = $config;
    }

    /**
     * Instantiate resolver class from class name
     *
     * @param string $resolverName
     * @return ResolverInterface
     * @throws GraphQlInputException
     */
    public function create($resolverName)
    {
        return $this->config->getResolverClass($resolverName);
    }
}
