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
namespace Magento\Framework\View\Design\Theme\Customization;

/**
 * Theme service file interface
 */
interface FileInterface
{
    /**
     * Get type of file
     *
     * @return string
     */
    public function getType();

    /**
     * Gets absolute path to a custom file
     *
     * @param \Magento\Framework\View\Design\Theme\FileInterface $file
     * @return string
     */
    public function getFullPath(\Magento\Framework\View\Design\Theme\FileInterface $file);

    /**
     * Creates new custom file and binds to concrete service model
     *
     * @return \Magento\Framework\View\Design\Theme\FileInterface
     */
    public function create();

    /**
     * Saves related data to custom file
     *
     * @param \Magento\Framework\View\Design\Theme\FileInterface $file
     * @return $this
     */
    public function save(\Magento\Framework\View\Design\Theme\FileInterface $file);

    /**
     * Deletes related data to custom file
     *
     * @param \Magento\Framework\View\Design\Theme\FileInterface $file
     * @return $this
     */
    public function delete(\Magento\Framework\View\Design\Theme\FileInterface $file);

    /**
     * Prepare file content before it will be saved
     *
     * @param \Magento\Framework\View\Design\Theme\FileInterface $file
     * @return $this
     */
    public function prepareFile(\Magento\Framework\View\Design\Theme\FileInterface $file);
}
