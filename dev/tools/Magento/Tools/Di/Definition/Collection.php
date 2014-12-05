<?php
/**
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Tools\Di\Definition;

class Collection
{
    /**
     * List of definitions
     *
     * @var array
     */
    private $definitions = [];

    /**
     * Returns definitions as [instance => list of arguments]
     *
     * @return array
     */
    public function getCollection()
    {
        return $this->definitions;
    }

    /**
     * Initializes collection with array of definitions
     *
     * @param array $definitions
     *
     * @return void
     */
    public function initialize($definitions)
    {
        $this->definitions = $definitions;
    }

    /**
     * Adds collection to current collection
     *
     * @param Collection $collection
     *
     * @return void
     */
    public function addCollection(Collection $collection)
    {
        $this->initialize(array_merge($this->getCollection(), $collection->getCollection()));
    }

    /**
     * Add new definition for instance
     *
     * @param string $instance
     * @param array $arguments
     *
     * @return void
     */
    public function addDefinition($instance, $arguments = [])
    {
        $this->definitions[$instance] = $arguments;
    }

    /**
     * Returns instance arguments
     *
     * @param string $instanceName
     * @return null
     */
    public function getInstanceArguments($instanceName)
    {
        return isset($this->definitions[$instanceName]) ? $this->definitions[$instanceName] : null;
    }

    /**
     * Returns instances names list
     *
     * @return array
     */
    public function getInstancesNamesList()
    {
        return array_keys($this->getCollection());
    }

    /**
     * Whether instance defined
     *
     * @param string $instanceName
     * @return bool
     */
    public function hasInstance($instanceName)
    {
        return isset($this->definitions[$instanceName]);
    }
}
