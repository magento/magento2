<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Mtf\EntryPoint;

use Magento\Mtf\ObjectManager;

/**
 * Class EntryPoint
 *
 * Application entry point, used to bootstrap and run application
 */
class EntryPoint
{
    /**
     * @var string
     */
    protected $_rootDir;

    /**
     * @var array
     */
    protected $_parameters;

    /**
     * Application object manager
     *
     * @var ObjectManager
     */
    protected $_locator;

    /**
     * @param string $rootDir
     * @param array $parameters
     * @param ObjectManager $objectManager
     */
    public function __construct(
        $rootDir,
        array $parameters = [],
        ObjectManager $objectManager = null
    ) {
        $this->_rootDir = $rootDir;
        $this->_parameters = $parameters;
        $this->_locator = $objectManager;
    }

    /**
     * Run a Mtf application
     *
     * @param string $applicationName
     * @param array $arguments
     * @return mixed
     * @throws \DomainException
     */
    public function run($applicationName, array $arguments = [])
    {
        try {
            if (!$this->_locator) {
                $locatorFactory = new \Magento\Mtf\ObjectManagerFactory();
                $this->_locator = $locatorFactory->create();
            }
            return $this->_locator->create($applicationName, $arguments)->launch();
        } catch (\Exception $exception) {
            $message = "Error happened during application run.\n";
            $message .= $exception->getMessage();
            throw new \DomainException($message);
        }
    }
}
