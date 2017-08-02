<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model;

/**
 * Class EntityRegistry
 * @since 2.1.0
 */
class EntityRegistry
{
    /**
     * @var array
     * @since 2.1.0
     */
    protected $registry = [];

    /**
     * Register entity
     *
     * @param string $entityType
     * @param string $identifier
     * @param object $entity
     * @return void
     * @since 2.1.0
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
     * @since 2.1.0
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
     * @since 2.1.0
     */
    public function remove($entityType, $identifier)
    {
        if (isset($this->registry[$entityType][$identifier])) {
            unset($this->registry[$entityType][$identifier]);
        }
        return true;
    }
}
