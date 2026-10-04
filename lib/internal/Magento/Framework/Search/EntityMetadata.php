<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\Search;

/**
 * Entity metadata
 * @api
 * @since 100.0.2
 */
class EntityMetadata
{
    /**
     * @var string
     */
    private $entityId;

    /**
     * @param string $entityId
     * @codeCoverageIgnore
     */
    public function __construct($entityId)
    {
        $this->entityId = $entityId;
    }

    /**
     * Get entity id
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getEntityId()
    {
        return $this->entityId;
    }
}
