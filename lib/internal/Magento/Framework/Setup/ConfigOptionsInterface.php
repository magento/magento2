<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup;

/**
 * Interface for handling options in deployment configuration tool
 */
interface ConfigOptionsInterface
{
    /**
     * Gets a list of input options so that user can provide required
     * information that will be used in deployment config file
     *
     * @return AbstractConfigOption[]
     */
    public function getOptions();

    /**
     * Creates config array from user inputted data. This array will be stored in deployment config file
     *
     * @param array $options
     * @return array
     * @throws \InvalidArgumentException
     */
    public function createConfig(array $options);
}
