<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Search;

/**
 * Entity metadata
 */
class EntityMetadata
{
    /**
     * @var string
     */
    private $entityId;

    /**
     * @param string $entityId
     */
    public function __construct($entityId)
    {
        $this->entityId = $entityId;
    }

    /**
     * Get entity id
     *
     * @return string
     */
    public function getEntityId()
    {
        return $this->entityId;
    }
}
