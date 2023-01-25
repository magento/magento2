<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaContentSynchronizationApi\Model;

/**
 * Configuration of entities used for media content.
 */
class GetEntities implements GetEntitiesInterface
{
    /**
     * @var array
     */
    private $entities;

    /**
     * @param array $entities
     */
    public function __construct(
        array $entities = []
    ) {
        $this->entities = $entities;
    }

    /**
     * Get all entities configuration  used in media content.
     *
     * @return array
     */
    public function execute(): array
    {
        return $this->entities;
    }
}
