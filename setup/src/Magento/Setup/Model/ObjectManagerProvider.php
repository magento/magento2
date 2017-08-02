<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model;

use Symfony\Component\Console\Application;
use Magento\Framework\Console\CommandListInterface;
use Magento\Framework\ObjectManagerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Magento\Setup\Mvc\Bootstrap\InitParamListener;

/**
 * Object manager provider
 *
 * Links Zend Framework's service locator and Magento object manager.
 * Guaranties single object manager per application run.
 * Hides complexity of creating Magento object manager
 * @since 2.0.0
 */
class ObjectManagerProvider
{
    /**
     * @var ServiceLocatorInterface
     * @since 2.0.0
     */
    private $serviceLocator;

    /**
     * @var ObjectManagerInterface
     * @since 2.0.0
     */
    private $objectManager;

    /**
     * @var Bootstrap
     * @since 2.2.0
     */
    private $bootstrap;

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @param Bootstrap $bootstrap
     * @since 2.0.0
     */
    public function __construct(
        ServiceLocatorInterface $serviceLocator,
        Bootstrap $bootstrap
    ) {
        $this->serviceLocator = $serviceLocator;
        $this->bootstrap = $bootstrap;
    }

    /**
     * Retrieve object manager.
     *
     * @return ObjectManagerInterface
     * @throws \Magento\Setup\Exception
     * @since 2.0.0
     */
    public function get()
    {
        if (null === $this->objectManager) {
            $initParams = $this->serviceLocator->get(InitParamListener::BOOTSTRAP_PARAM);
            $factory = $this->getObjectManagerFactory($initParams);
            $this->objectManager = $factory->create($initParams);
            if (PHP_SAPI == 'cli') {
                $this->createCliCommands();
            }
        }
        return $this->objectManager;
    }

    /**
     * Creates cli commands and initialize them with application instance
     *
     * @return void
     * @since 2.2.0
     */
    private function createCliCommands()
    {
        /** @var CommandListInterface $commandList */
        $commandList = $this->objectManager->create(CommandListInterface::class);
        foreach ($commandList->getCommands() as $command) {
            $command->setApplication(
                $this->serviceLocator->get(Application::class)
            );
        }
    }

    /**
     * Causes object manager to be reinitialized the next time it is retrieved.
     *
     * @return void
     * @since 2.0.0
     */
    public function reset()
    {
        $this->objectManager = null;
    }

    /**
     * Sets object manager
     *
     * @param ObjectManagerInterface $objectManager
     * @return void
     * @since 2.0.0
     */
    public function setObjectManager(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Returns ObjectManagerFactory
     *
     * @param array $initParams
     * @return \Magento\Framework\App\ObjectManagerFactory
     * @since 2.0.0
     */
    public function getObjectManagerFactory($initParams = [])
    {
        return $this->bootstrap->createObjectManagerFactory(
            BP,
            $initParams
        );
    }
}
