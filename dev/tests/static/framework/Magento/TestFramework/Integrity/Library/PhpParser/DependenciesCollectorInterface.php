<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\TestFramework\Integrity\Library\PhpParser;

/**
 * Collect dependencies
 *
 */
interface DependenciesCollectorInterface
{
    /**
     * Return list of dependencies
     *
     * @param Uses $uses
     * @return string[]
     */
    public function getDependencies(Uses $uses);
}
