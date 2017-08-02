<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Search;

/**
 * Entity metadata
 * @api
 * @since 2.0.0
 */
class EntityMetadata
{
    /**
     * @var string
     * @since 2.0.0
     */
    private $entityId;

    /**
     * @param string $entityId
     * @codeCoverageIgnore
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getEntityId()
    {
        return $this->entityId;
    }
}
