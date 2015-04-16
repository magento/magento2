<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Console;

use Magento\Framework\App\Bootstrap;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\DeploymentConfig;

/**
 * Providing object manager for a specific area
 */
class ObjectManagerProvider
{
    /**
     * Object manager
     *
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Parameters
     *
     * @var ParameterInterface
     */
    private $params;

    /**
     * @param ParameterInterface $params
     */
    public function __construct(ParameterInterface $params)
    {
        $this->params = $params;
    }

    /**
     * Retrieve object manager.
     *
     * @return ObjectManagerInterface
     */
    public function get()
    {
        if (null === $this->objectManager) {
            $initParams = $this->params->getCustomParameters();
            $factory = Bootstrap::createObjectManagerFactory(BP, $initParams);
            $this->objectManager = $factory->create($initParams);
        }
        return $this->objectManager;
    }

    /**
     * Causes object manager to be reinitialized the next time it is retrieved.
     *
     * @return void
     */
    public function reset()
    {
        $this->objectManager = null;
    }
}
