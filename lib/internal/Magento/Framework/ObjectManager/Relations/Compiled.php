<?php
/**
 * List of parent classes with their parents and interfaces
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\ObjectManager\Relations;

class Compiled implements \Magento\Framework\ObjectManager\RelationsInterface
{
    /**
     * List of class relations
     *
     * @var array
     */
    protected $_relations;

    /**
     * Default relation list
     *
     * @var array
     */
    protected $_default = [];

    /**
     * @param array $relations
     */
    public function __construct(array $relations)
    {
        $this->_relations = $relations;
    }

    /**
     * Check whether requested type is available for read
     *
     * @param string $type
     * @return bool
     */
    public function has($type)
    {
        return isset($this->_relations[$type]);
    }

    /**
     * Retrieve parents for class
     *
     * @param string $type
     * @return array
     */
    public function getParents($type)
    {
        return $this->_relations[$type];
    }
}
