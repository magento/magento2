<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App;

use Magento\Framework\ObjectManager\FactoryInterface;
use Magento\Framework\Interception\ObjectManager\ConfigInterface;
use Magento\Framework\ObjectManager\ConfigLoaderInterface;

/**
 * Interface for ObjectManager Environment
 * @since 2.0.0
 */
interface EnvironmentInterface
{
    /**
     * Return name of running mode
     *
     * @return string
     * @since 2.0.0
     */
    public function getMode();

    /**
     * Return config object
     *
     * @return ConfigInterface
     * @since 2.0.0
     */
    public function getDiConfig();

    /**
     * Return factory object
     *
     * @param array $arguments
     * @return FactoryInterface
     * @since 2.0.0
     */
    public function getObjectManagerFactory($arguments);

    /**
     * Return ConfigLoader object
     *
     * @return ConfigLoaderInterface
     * @since 2.0.0
     */
    public function getObjectManagerConfigLoader();

    /**
     * @param ConfigInterface $diConfig
     * @param array &$sharedInstances
     * @return void
     * @since 2.0.0
     */
    public function configureObjectManager(ConfigInterface $diConfig, &$sharedInstances);
}
