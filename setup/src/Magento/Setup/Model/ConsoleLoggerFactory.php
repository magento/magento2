<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model;

use Symfony\Component\Console\Output\OutputInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ConsoleLoggerFactory
{
    /**
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
     * @param OutputInterface $output
     * @return ConsoleLogger
     */
    public function create(OutputInterface $output)
    {
        return new ConsoleLogger($output);
    }
}
