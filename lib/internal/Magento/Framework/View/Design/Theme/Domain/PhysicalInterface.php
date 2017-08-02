<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Design\Theme\Domain;

/**
 * Interface PhysicalInterface
 * @since 2.0.0
 */
interface PhysicalInterface
{
    /**
     * Create theme customization
     *
     * @param \Magento\Framework\View\Design\ThemeInterface $theme
     * @return \Magento\Framework\View\Design\ThemeInterface
     * @since 2.0.0
     */
    public function createVirtualTheme($theme);
}
