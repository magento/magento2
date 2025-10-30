<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Setup\Mvc;

/**
 * Native MvcEvent class.
 * Magento setup commands without requiring Laminas mvc dependencies
 */
class MvcEvent
{
    public const EVENT_BOOTSTRAP = 'bootstrap';
    /**
     * @var MvcApplication
     */
    private MvcApplication $application;

    /**
     * Set application
     *
     * @param MvcApplication $application
     * @return void
     */
    public function setApplication(MvcApplication $application): void
    {
        $this->application = $application;
    }

    /**
     * Get application
     *
     * @return MvcApplication
     */
    public function getApplication(): MvcApplication
    {
        return $this->application;
    }
}
