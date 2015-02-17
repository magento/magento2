<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Module;

use Magento\Framework\Setup\ModuleSchemaSetupInterface;
use Magento\Setup\Model\LoggerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class SetupFactory
{
    /**
     * @var ServiceLocatorInterface
     */
    private $serviceLocator;

    /**
     * Constructor
     *
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function __construct(
        ServiceLocatorInterface $serviceLocator
    ) {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * Creates Setup
     *
     * @param \Magento\Framework\App\Resource $resource
     * @return Setup
     */
    public function createSetup($resource)
    {
        return new Setup($resource);
    }
}
