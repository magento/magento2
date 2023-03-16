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
 */
class EntityPool
{
    /**
     * @param array $entities
     */
    public function __construct(
        protected readonly array $entities = []
    ) {
    }

    /**
     * Retrieve entities that require sequence
     *
     * @return array
     */
    public function getEntities()
    {
        return $this->entities;
    }
}
