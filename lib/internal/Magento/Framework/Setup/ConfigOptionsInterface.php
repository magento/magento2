<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup;

/**
 * Interface for segment in deployment configuration
 */
interface ConfigOptionsInterface
{
    /**
     * Gets user input options to a segment in deployment configuration
     *
     * @return AbstractConfigOption[]
     */
    public function getOptions();

    /**
     * Creates deployment configuration options array that will be stored in deployment config file
     *
     * @param array $options
     * @return array
     * @throws \InvalidArgumentException
     */
    public function createConfig(array $options);
}
