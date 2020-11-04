<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Module;

use Laminas\ServiceManager\ServiceLocatorInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ResourceConnection;
use Magento\Setup\Module\Setup\ResourceConfig;

/**
 * Factory for Magento\Framework\App\ResourceConnection
 */
class ResourceFactory
{
    /**
     * Laminas Framework's service locator
     *
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;

    /**
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function __construct(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * Create object
     *
     * @param DeploymentConfig $deploymentConfig
     * @return ResourceConnection
     */
    public function create(DeploymentConfig $deploymentConfig)
    {
        $connectionFactory = $this->serviceLocator->get(ConnectionFactory::class);
        $resource = new ResourceConnection(
            new ResourceConfig(),
            $connectionFactory,
            $deploymentConfig
        );

        return $resource;
    }
}
