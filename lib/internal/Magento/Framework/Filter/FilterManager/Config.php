<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filter\FilterManager;

/**
 * Filter plugin manager config
 * @since 2.0.0
 */
class Config implements ConfigInterface
{
    /**
     * @var string[]
     * @since 2.0.0
     */
    protected $factories = [\Magento\Framework\Filter\Factory::class, \Magento\Framework\Filter\ZendFactory::class];

    /**
     * @param string[] $factories
     * @since 2.0.0
     */
    public function __construct(array $factories = [])
    {
        if (!empty($factories)) {
            $this->factories = array_merge($factories, $this->factories);
        }
    }

    /**
     * Get list of factories
     *
     * @return string[]
     * @since 2.0.0
     */
    public function getFactories()
    {
        return $this->factories;
    }
}
