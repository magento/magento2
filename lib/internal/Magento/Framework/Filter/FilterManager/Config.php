<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filter\FilterManager;

use Magento\Framework\Filter\Factory;
use Magento\Framework\Filter\LaminasFactory;

/**
 * Filter plugin manager config
 */
class Config implements ConfigInterface
{
    /**
     * @var string[]
     */
    protected $factories = [Factory::class, LaminasFactory::class];

    /**
     * @param string[] $factories
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
     */
    public function getFactories()
    {
        return $this->factories;
    }
}
