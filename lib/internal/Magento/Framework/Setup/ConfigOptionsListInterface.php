<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup;

use Magento\Framework\App\DeploymentConfig;

/**
 * Interface for handling options in deployment configuration tool
 */
interface ConfigOptionsListInterface
{
    /**
     * Gets a list of input options so that user can provide required
     * information that will be used in deployment config file
     *
     * @return Option\AbstractConfigOption[]
     */
    public function getOptions();

    /**
     * Creates array of ConfigData objects from user input data.
     * Data in these objects will be stored in array form in deployment config file.
     *
     * @param array $options
     * @param DeploymentConfig $deploymentConfig
     * @return \Magento\Framework\Config\Data\ConfigData[]
     */
    public function createConfig(array $options, DeploymentConfig $deploymentConfig);

    /**
     * Validates user input option values and returns error messages
     *
     * @param array $options
     * @param DeploymentConfig $deploymentConfig
     * @return string[]
     */
    public function validate(array $options, DeploymentConfig $deploymentConfig);
}
