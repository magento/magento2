<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Design\Theme\Customization;

/**
 * Theme service file interface
 * @since 2.0.0
 */
interface FileInterface
{
    /**
     * Get type of file
     *
     * @return string
     * @since 2.0.0
     */
    public function getType();

    /**
     * Gets absolute path to a custom file
     *
     * @param \Magento\Framework\View\Design\Theme\FileInterface $file
     * @return string
     * @since 2.0.0
     */
    public function getFullPath(\Magento\Framework\View\Design\Theme\FileInterface $file);

    /**
     * Creates new custom file and binds to concrete service model
     *
     * @return \Magento\Framework\View\Design\Theme\FileInterface
     * @since 2.0.0
     */
    public function create();

    /**
     * Saves related data to custom file
     *
     * @param \Magento\Framework\View\Design\Theme\FileInterface $file
     * @return $this
     * @since 2.0.0
     */
    public function save(\Magento\Framework\View\Design\Theme\FileInterface $file);

    /**
     * Deletes related data to custom file
     *
     * @param \Magento\Framework\View\Design\Theme\FileInterface $file
     * @return $this
     * @since 2.0.0
     */
    public function delete(\Magento\Framework\View\Design\Theme\FileInterface $file);

    /**
     * Prepare file content before it will be saved
     *
     * @param \Magento\Framework\View\Design\Theme\FileInterface $file
     * @return $this
     * @since 2.0.0
     */
    public function prepareFile(\Magento\Framework\View\Design\Theme\FileInterface $file);
}
