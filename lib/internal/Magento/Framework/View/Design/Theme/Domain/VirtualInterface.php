<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Design\Theme\Domain;

/**
 * Interface VirtualInterface
 */
interface VirtualInterface
{
    /**
     * Get 'staging' theme
     *
     * @return \Magento\Framework\View\Design\ThemeInterface
     */
    public function getStagingTheme();

    /**
     * Get 'physical' theme
     *
     * @return \Magento\Framework\View\Design\ThemeInterface
     */
    public function getPhysicalTheme();
}
