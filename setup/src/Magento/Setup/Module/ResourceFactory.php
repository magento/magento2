<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module;

use Magento\Framework\App\ResourceConnection;
use Magento\Setup\Module\Setup\ResourceConfig;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class \Magento\Setup\Module\ResourceFactory
 *
 * @since 2.0.0
 */
class ResourceFactory
{
    /**
     * Zend Framework's service locator
     *
     * @var ServiceLocatorInterface
     * @since 2.0.0
     */
    protected $serviceLocator;

    /**
     * Constructor
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @since 2.0.0
     */
    public function __construct(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * @param \Magento\Framework\App\DeploymentConfig $deploymentConfig
     * @return Resource
     * @since 2.0.0
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
