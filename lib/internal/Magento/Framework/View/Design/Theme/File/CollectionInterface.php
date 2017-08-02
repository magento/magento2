<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Design\Theme\File;

/**
 * Design Theme File collection interface
 * @since 2.0.0
 */
interface CollectionInterface
{
    /**
     * Get items
     *
     * @return \Magento\Framework\View\Design\Theme\FileInterface[]
     * @since 2.0.0
     */
    public function getItems();

    /**
     * Filter out files that do not belong to a theme
     *
     * @param \Magento\Framework\View\Design\ThemeInterface $theme
     * @return CollectionInterface
     * @since 2.0.0
     */
    public function addThemeFilter(\Magento\Framework\View\Design\ThemeInterface $theme);

    /**
     * Set default order
     *
     * @param string $direction
     * @return CollectionInterface
     * @since 2.0.0
     */
    public function setDefaultOrder($direction = 'ASC');

    /**
     * Add field filter to collection
     *
     * @param string $field
     * @param null|string|array $condition
     * @return CollectionInterface
     * @since 2.0.0
     */
    public function addFieldToFilter($field, $condition = null);
}
