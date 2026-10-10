<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Session;

/**
 * Session save handlers that can open a session without acquiring a write lock.
 */
interface SessionAccessModeAwareInterface
{
    /**
     * Sets whether the next session open is read-only.
     *
     * @param bool $readOnly
     * @return void
     */
    public function setReadOnly(bool $readOnly): void;
}
