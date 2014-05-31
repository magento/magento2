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
namespace Magento\Framework\View\Design\Theme;

/**
 * Theme customization interface
 */
interface CustomizationInterface
{
    /**
     * Retrieve list of files which belong to a theme
     *
     * @return \Magento\Framework\View\Design\Theme\Customization\FileInterface[]
     */
    public function getFiles();

    /**
     * Retrieve list of files which belong to a theme only by type
     *
     * @param string $type
     * @return \Magento\Framework\View\Design\Theme\Customization\FileInterface[]
     */
    public function getFilesByType($type);

    /**
     * Returns customization absolute path
     *
     * @return string
     */
    public function getCustomizationPath();

    /**
     * Get directory where themes files are stored
     *
     * @return string
     */
    public function getThemeFilesPath();

    /**
     * Get path to custom view configuration file
     *
     * @return string
     */
    public function getCustomViewConfigPath();

    /**
     * Reorder files positions
     *
     * @param string $type
     * @param array $sequence
     * @return CustomizationInterface
     */
    public function reorder($type, array $sequence);

    /**
     * Remove custom files by ids
     *
     * @param array $fileIds
     * @return $this
     */
    public function delete(array $fileIds);
}
