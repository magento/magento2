<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Session\Test\Unit\Helper;

use Magento\Framework\Session\SessionManager;

/**
 * Test helper for SessionManager with custom affectedItems methods
 *
 * Only implements custom methods not available in SessionManager
 */
class SessionManagerTestHelper extends SessionManager
{
    /**
     * @var array|null
     */
    private $affectedItems = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        // Skip parent constructor to avoid dependencies
    }

    /**
     * Get affected items
     *
     * @return array|null
     */
    public function getAffectedItems()
    {
        return $this->affectedItems;
    }

    /**
     * Set affected items
     *
     * @param mixed $items
     * @return void
     */
    public function setAffectedItems($items)
    {
        if (is_array($items)) {
            $this->affectedItems = $items;
        } else {
            $this->affectedItems = null;
        }
    }
}
