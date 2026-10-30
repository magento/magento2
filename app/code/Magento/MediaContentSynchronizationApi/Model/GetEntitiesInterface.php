<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\MediaContentSynchronizationApi\Model;

/**
 * Get entities for media content by provided configuration.
 * @api
 */
interface GetEntitiesInterface
{
    /**
     * Get entities that used for media content
     *
     * @return array
     */
    public function execute(): array;
}
