<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\View\Design\Theme\File;

/**
 * Design Theme File collection interface
 */
interface CollectionInterface
{
    /**
     * Get items
     *
     * @return \Magento\Framework\View\Design\Theme\FileInterface[]
     */
    public function getItems();

    /**
     * Filter out files that do not belong to a theme
     *
     * @param \Magento\Framework\View\Design\ThemeInterface $theme
     * @return CollectionInterface
     */
    public function addThemeFilter(\Magento\Framework\View\Design\ThemeInterface $theme);

    /**
     * Set default order
     *
     * @param string $direction
     * @return CollectionInterface
     */
    public function setDefaultOrder($direction = 'ASC');

    /**
     * Add field filter to collection
     *
     * @param string $field
     * @param null|string|array $condition
     * @return CollectionInterface
     */
    public function addFieldToFilter($field, $condition = null);
}
