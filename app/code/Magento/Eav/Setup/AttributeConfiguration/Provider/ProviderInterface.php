<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Setup\AttributeConfiguration\Provider;

/**
 * The Attribute configuration provider resolves passed values
 * to valid configuration options.
 *
 * E.g. A "frontend input" configuration provider can resolve
 * various input formats to ones that the system can use.
 */
interface ProviderInterface
{
    /**
     * @param mixed $attributeConfiguration
     * @return bool
     */
    public function exists($attributeConfiguration);

    /**
     * Convert the provided value to one expected by the consumers.
     *
     * An exception should be thrown when the provider cannot resolve
     * the value. If such behaviour is undesired, client code should
     * use exist() first. If exist() returns true for a value,
     * this method must not throw when the same value is passed.
     *
     * @param mixed $attributeConfiguration
     * @return mixed
     * @throws \Magento\Eav\Setup\AttributeConfiguration\InvalidConfigurationException
     *         If the provided configuration does not exist.
     */
    public function resolve($attributeConfiguration);
}
