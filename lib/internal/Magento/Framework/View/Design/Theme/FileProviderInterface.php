<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\View\Design\Theme;

/**
 * Theme files provider
 *
 * @api
 * @since 100.0.2
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
