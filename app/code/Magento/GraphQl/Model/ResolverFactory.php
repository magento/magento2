<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQl\Model;

use Magento\Framework\ObjectManagerInterface;
use Magento\GraphQl\Model\Query\Config;

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
     * @var Config
     */
    private $config;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager, Config $config)
    {
        $this->objectManager = $objectManager;
        $this->config = $config;
    }

    /**
     * Instantiate resolver class from class name
     *
     * @param string $resolverName
     * @return ResolverInterface
     */
    public function create($resolverName)
    {
        return $this->config->getResolverClass($resolverName);
    }
}
