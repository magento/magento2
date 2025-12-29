<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Test\Unit\Helper;

use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Page\Config;

/**
 * Test helper for Page
 *
 * This helper extends the concrete Page class to provide
 * test-specific functionality without dependency injection issues.
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class PageTestHelper extends Page
{
    /**
     * @var Config
     */
    private $config;

    /**
     * Constructor that accepts config
     *
     * @param Config $config
     */
    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * Set active menu
     *
     * @param string $menuId
     * @return $this
     */
    public function setActiveMenu($menuId)
    {
        return $this;
    }

    /**
     * Add breadcrumb
     *
     * @param string $label
     * @param string $title
     * @param string|null $link
     * @return $this
     */
    public function addBreadcrumb($label, $title, $link = null)
    {
        return $this;
    }

    /**
     * Get config
     *
     * @return Config
     */
    public function getConfig()
    {
        return $this->config;
    }
}
