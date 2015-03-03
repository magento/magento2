<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup;

interface OptionsInterface
{
    /**
     * Gets deployment configuration options of a module
     *
     * @return Option[]
     */
    public function getDeploymentConfigOptions();

    /**
     * Creates deployment configuration options array that will be stored in deployment config file
     *
     * @param array $options
     * @return array
     */
    public function createDeploymentConfig(array $options);
}
