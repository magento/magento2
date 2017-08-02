<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesSequence\Model;

/**
 * Class EntityPool
 *
 * Pool of entities that require sequence
 * @since 2.0.0
 */
class EntityPool
{
    /**
     * @var array
     * @since 2.0.0
     */
    protected $entities;

    /**
     * @param array $entities
     * @since 2.0.0
     */
    public function __construct(array $entities = [])
    {
        $this->entities = $entities;
    }

    /**
     * Retrieve entities that require sequence
     *
     * @return array
     * @since 2.0.0
     */
    public function getEntities()
    {
        return $this->entities;
    }
}
