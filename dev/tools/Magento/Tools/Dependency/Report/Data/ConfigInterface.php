<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Tools\Dependency\Report\Data;

/**
 * Config
 */
interface ConfigInterface
{
    /**
     * Get modules
     *
     * @return array
     */
    public function getModules();

    /**
     * Get total dependencies count
     *
     * @return int
     */
    public function getDependenciesCount();
}
