<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App;

use Magento\Framework\ObjectManager\FactoryInterface;
use Magento\Framework\Interception\ObjectManager\ConfigInterface;
use Magento\Framework\ObjectManager\ConfigLoaderInterface;

/**
 * Interface for ObjectManager Environment
 *
 * @api
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
     * @param ConfigInterface $diConfig
     * @param array &$sharedInstances
     * @return void
     */
    public function configureObjectManager(ConfigInterface $diConfig, &$sharedInstances);
}
