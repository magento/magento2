<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\MessageQueue;

/**
 * Simple DTO used by message queue integration tests.
 */
class ReadonlyMessage
{
    /**
     * @param int $entityId
     * @param string $name
     */
    public function __construct(
        public readonly int $entityId,
        public readonly string $name
    ) {
    }
}
