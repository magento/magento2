<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Test\Unit\Helper;

use Magento\Framework\View\Element\AbstractBlock;

/**
 * Test helper for AbstractBlock
 *
 * This helper extends the concrete AbstractBlock class to provide a minimal
 * block implementation for testing without dependency injection issues.
 *
 * Custom Methods:
 * - toHtml() - Required override of abstract parent method, returns test HTML
 *
 * Inherited Methods (from AbstractBlock):
 * - All standard block methods are available via inheritance
 *
 * Used by tests that need a simple block instance without complex initialization.
 */
class AbstractBlockTestHelper extends AbstractBlock
{
    /**
     * Constructor that skips parent initialization
     */
    public function __construct()
    {
        // Skip parent constructor to avoid dependency injection issues
    }

    /**
     * Convert to HTML
     *
     * Overrides abstract parent method to provide simple test output.
     * Parent implementation requires layout, event manager, and scope config.
     *
     * @return string
     */
    public function toHtml()
    {
        return '<span>test Message</span>';
    }
}
