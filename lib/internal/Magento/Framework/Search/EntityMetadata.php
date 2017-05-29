<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
