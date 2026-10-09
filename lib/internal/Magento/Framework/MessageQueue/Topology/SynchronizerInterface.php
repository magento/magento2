<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\MessageQueue\Topology;

/**
 * Applies the configured message queue topology to its backend.
 */
interface SynchronizerInterface
{
    /**
     * Apply the configured topology to its backend.
     *
     * @return string[] Descriptions of the topology applied, empty when the backend had nothing to apply.
     *                  A backend whose apply operation is idempotent and reports nothing about prior
     *                  state describes everything it applied, so a non-empty result is not proof of a change.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function synchronize(): array;
}
