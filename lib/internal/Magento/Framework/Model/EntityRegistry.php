<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model;

/**
 * Class EntityRegistry
 */
class EntityRegistry
{
    /**
     * @var array
     */
    protected $registry = [];

    /**
     * Register entity
     *
     * @param string $entityType
     * @param string $identifier
     * @param object $entity
     * @return void
     */
    public function register($entityType, $identifier, $entity)
    {
        $this->registry[$entityType][$identifier] = $entity;
    }

    /**
     * Retrieve entity from storage
     *
     * @param string $entityType
     * @param string $identifier
     * @return null|object
     */
    public function retrieve($entityType, $identifier)
    {
        if (isset($this->registry[$entityType][$identifier])) {
            return $this->registry[$entityType][$identifier];
        } else {
            return null;
        }
    }

    /**
     * Remove entity from registry
     *
     * @param string $entityType
     * @param string $identifier
     * @return bool
     */
    public function remove($entityType, $identifier)
    {
        if (isset($this->registry[$entityType][$identifier])) {
            unset($this->registry[$entityType][$identifier]);
        }
        return true;
    }
}
