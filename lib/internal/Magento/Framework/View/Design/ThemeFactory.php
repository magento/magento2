<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Design;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class ThemeFactory
 *
 * Minimal required interface a theme has to implement
 * @since 2.0.0
 */
class ThemeFactory
{
    /**
     * Object manager
     *
     * @var ObjectManagerInterface
     * @since 2.0.0
     */
    protected $objectManager;

    /**
     * Constructor
     *
     * @param ObjectManagerInterface $objectManager
     * @since 2.0.0
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Get theme
     *
     * @param int $themeId
     * @return null|\Magento\Framework\View\Design\ThemeInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function getTheme($themeId)
    {
        return null;
    }
}
