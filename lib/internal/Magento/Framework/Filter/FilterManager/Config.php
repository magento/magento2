<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\Filter\FilterManager;

/**
 * Filter plugin manager config
 */
class Config implements ConfigInterface
{
    /**
     * @var string[]
     */
    protected $factories = ['Magento\Framework\Filter\Factory', 'Magento\Framework\Filter\ZendFactory'];

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
