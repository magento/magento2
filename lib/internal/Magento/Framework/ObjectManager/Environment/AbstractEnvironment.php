<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\ObjectManager\Environment;

use Magento\Framework\ObjectManager\EnvironmentFactory;
use Magento\Framework\ObjectManager\EnvironmentInterface;
use Magento\Framework\ObjectManager\Profiler\FactoryDecorator;
use Magento\Framework\ObjectManager\FactoryInterface;

abstract class AbstractEnvironment implements EnvironmentInterface
{
    /**
     * @var \Magento\Framework\Interception\ObjectManager\ConfigInterface
     */
    protected $config;

    /**
     * Mode name
     */
    protected $mode = 'developer';

    /**
     * @var string
     */
    protected $configPreference = 'Magento\Framework\ObjectManager\Factory\Dynamic\Developer';

    /**
     * @var FactoryInterface
     */
    protected $factory;

    /**
     * @var EnvironmentFactory
     */
    protected $envFactory;

    /**
     * @param EnvironmentFactory $envFactory
     */
    public function __construct(EnvironmentFactory $envFactory)
    {
        $this->envFactory = $envFactory;
    }

    /**
     * Returns object manager factory
     *
     * @param array $arguments
     * @return \Magento\Framework\ObjectManager\Factory\AbstractFactory
     */
    public function getObjectManagerFactory($arguments)
    {
        $factoryClass = $this->getDiConfig()->getPreference($this->configPreference);

        $this->factory = $this->createFactory($arguments, $factoryClass);
        $this->decorate($arguments);

        return $this->factory;
    }

    /**
     * Return name of running mode
     *
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * Decorate factory
     *
     * @param array $arguments
     * @return void
     */
    protected function decorate($arguments)
    {
        if (isset($arguments['MAGE_PROFILER']) && $arguments['MAGE_PROFILER'] == 2) {
            $this->factory = new FactoryDecorator(
                $this->factory,
                \Magento\Framework\ObjectManager\Profiler\Log::getInstance()
            );
        }
    }

    /**
     * Creates factory
     *
     * @param array $arguments
     * @param string $factoryClass
     *
     * @return FactoryInterface
     */
    protected function createFactory($arguments, $factoryClass)
    {
        return new $factoryClass(
            $this->getDiConfig(),
            null,
            $this->envFactory->getDefinitions(),
            $arguments
        );
    }
}
