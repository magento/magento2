<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\ObjectManager\Environment;

use Magento\Framework\App\EnvironmentFactory;
use Magento\Framework\Interception\ObjectManager\ConfigInterface;
use Magento\Framework\App\EnvironmentInterface;
use Magento\Framework\ObjectManager\Profiler\FactoryDecorator;
use Magento\Framework\ObjectManager\FactoryInterface;
use Magento\Framework\ObjectManager\Profiler\Log;

/**
 * Class \Magento\Framework\App\ObjectManager\Environment\AbstractEnvironment
 *
 * @since 2.0.0
 */
abstract class AbstractEnvironment implements EnvironmentInterface
{
    /**
     * @var ConfigInterface
     * @since 2.0.0
     */
    protected $config;

    /**
     * Mode name
     * @since 2.0.0
     */
    protected $mode = 'developer';

    /**
     * @var string
     * @since 2.0.0
     */
    protected $configPreference = \Magento\Framework\ObjectManager\Factory\Dynamic\Developer::class;

    /**
     * @var FactoryInterface
     * @since 2.0.0
     */
    protected $factory;

    /**
     * @var EnvironmentFactory
     * @since 2.0.0
     */
    protected $envFactory;

    /**
     * @param EnvironmentFactory $envFactory
     * @since 2.0.0
     */
    public function __construct(EnvironmentFactory $envFactory)
    {
        $this->envFactory = $envFactory;
    }

    /**
     * Returns object manager factory
     *
     * @param array $arguments
     * @return FactoryInterface
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    protected function decorate($arguments)
    {
        if (isset($arguments['MAGE_PROFILER']) && $arguments['MAGE_PROFILER'] == 2) {
            $this->factory = new FactoryDecorator(
                $this->factory,
                Log::getInstance()
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
     * @since 2.0.0
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
