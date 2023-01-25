<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\TestFramework\Unit\Listener;

use PHPUnit\Framework\TestListener;
use PHPUnit\Framework\TestSuite;

/**
 * Listener of PHPUnit built-in events that enforces cleanup of cyclic object references
 *
 */
class GarbageCleanup implements TestListener
{
    use \PHPUnit\Framework\TestListenerDefaultImplementation;

    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function endTestSuite(TestSuite $suite): void
    {
        gc_collect_cycles();
    }
}
