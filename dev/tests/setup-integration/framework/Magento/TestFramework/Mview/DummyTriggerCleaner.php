<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Mview;

/**
 * Stub for \Magento\Framework\Mview\TriggerCleaner
 */
class DummyTriggerCleaner
{
    /**
     * Remove the outdated trigger from the system
     *
     * @return bool
     */
    public function removeTriggers(): bool
    {
        return true;
    }
}
