<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\Filter\FilterManager;

/**
 * Filter manager config interface
 */
interface ConfigInterface
{
    /**
     * Get list of factories
     *
     * @return string[]
     */
    public function getFactories();
}
