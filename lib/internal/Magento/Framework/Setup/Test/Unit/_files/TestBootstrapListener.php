<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Setup\Mvc;

use Magento\Framework\Setup\Mvc\MvcApplication;
use Magento\Framework\Setup\Mvc\MvcEvent;

class TestBootstrapListener
{
    /**
     * Flag to track if bootstrap event was triggered
     *
     * @var bool
     */
    public static bool $bootstrapped = false;

    public function onBootstrap(MvcEvent $event): void
    {
        self::$bootstrapped = $event->getApplication() instanceof MvcApplication;
    }
}
