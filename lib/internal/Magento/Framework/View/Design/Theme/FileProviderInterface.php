<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Design\Theme;

/**
 * Interface FileProviderInterface
 */
interface FileProviderInterface
{
    /**
     * Get items
     *
     * @param \Magento\Framework\View\Design\ThemeInterface $theme
     * @param array $filters
     * @return \Magento\Framework\View\Design\Theme\FileInterface[]
     */
    public function getItems(\Magento\Framework\View\Design\ThemeInterface $theme, array $filters = []);
}
