<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
