<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Session\Test\Unit;

use Magento\Framework\Session\SessionManager;
use PHPUnit\Framework\TestCase;

class SessionManagerShutdownTest extends TestCase
{
    /**
     * The shutdown function must not keep a session alive once nothing else uses it.
     *
     * @return void
     */
    public function testRegisteredShutdownFunctionDoesNotKeepTheSessionAlive(): void
    {
        $session = (new \ReflectionClass(SessionManager::class))->newInstanceWithoutConstructor();
        $reference = \WeakReference::create($session);

        $session->registerShutdown();
        unset($session);
        gc_collect_cycles();

        $this->assertNull($reference->get());
    }
}
