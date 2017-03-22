<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module;

use Magento\Framework\App\ResourceConnection;
use Magento\Setup\Module\Setup\ResourceConfig;
use Zend\ServiceManager\ServiceLocatorInterface;

class ResourceFactory
{
    /**
     * Zend Framework's service locator
     *
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;

    /**
     * Constructor
     *
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function __construct(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * @param \Magento\Framework\App\DeploymentConfig $deploymentConfig
     * @return Resource
     */
    public function create(\Magento\Framework\App\DeploymentConfig $deploymentConfig)
    {
        $connectionFactory = $this->serviceLocator->get(\Magento\Setup\Module\ConnectionFactory::class);
        $resource = new ResourceConnection(
            new ResourceConfig(),
            $connectionFactory,
            $deploymentConfig
        );
        return $resource;
    }
}
