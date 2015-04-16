<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Console;

use Magento\Framework\Console\ParameterInterface;
use Magento\Setup\Mvc\Bootstrap\InitParamListener;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class implementing custom environment variables required for console command.
 */
class CustomParameters implements ParameterInterface
{
    /**
     * @var ServiceLocatorInterface
     */
    private $serviceLocator;

    /**
     * Constructor
     */
    public function __construct(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * Returns custom parameters for console
     *
     * @return array
     */
    public function getCustomParameters()
    {
        return  $this->serviceLocator->get(InitParamListener::BOOTSTRAP_PARAM);
    }
}
