<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Design\Theme\Domain;

/**
 * Interface VirtualInterface
 * @since 2.0.0
 */
interface VirtualInterface
{
    /**
     * Get 'staging' theme
     *
     * @return \Magento\Framework\View\Design\ThemeInterface
     * @since 2.0.0
     */
    public function getStagingTheme();

    /**
     * Get 'physical' theme
     *
     * @return \Magento\Framework\View\Design\ThemeInterface
     * @since 2.0.0
     */
    public function getPhysicalTheme();
}
