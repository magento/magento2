<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\View\Design\Theme\Domain;

/**
 * Interface PhysicalInterface
 */
interface PhysicalInterface
{
    /**
     * Create theme customization
     *
     * @param \Magento\Framework\View\Design\ThemeInterface $theme
     * @return \Magento\Framework\View\Design\ThemeInterface
     */
    public function createVirtualTheme($theme);
}
