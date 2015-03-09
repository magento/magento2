<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App;

use Magento\Framework\ObjectManager\FactoryInterface;
use Magento\Framework\Interception\ObjectManager\ConfigInterface;
use Magento\Framework\ObjectManager\ConfigLoaderInterface;

/**
 * Interface for ObjectManager Environment
 */
interface EnvironmentInterface
{
    /**
     * Return name of running mode
     *
     * @return string
     */
    public function getMode();

    /**
     * Return config object
     *
     * @return ConfigInterface
     */
    public function getDiConfig();

    /**
     * Return factory object
     *
     * @param array $arguments
     * @return FactoryInterface
     */
    public function getObjectManagerFactory($arguments);

    /**
     * Return ConfigLoader object
     *
     * @return ConfigLoaderInterface
     */
    public function getObjectManagerConfigLoader();

    /**
     * Configures an existing object manager
     *
     * @param ConfigInterface $diConfig
     * @return void
     */
    public function configureObjectManager(ConfigInterface $diConfig);
}
