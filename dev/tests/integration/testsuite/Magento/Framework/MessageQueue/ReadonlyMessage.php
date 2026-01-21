<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\MessageQueue;

class ReadonlyMessage
{
    public function __construct(
        public readonly int $entityId,
        public readonly string $name
    ) {
    }
}
