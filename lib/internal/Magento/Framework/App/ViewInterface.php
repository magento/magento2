<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

/**
 * Introduced as a facade for presentation related operations.
 * Later replaced with Magento\Framework\View\Result component
 *
 * @api
 * @deprecated 2.2.0 since 2.2.0
 * @see \Magento\Framework\View\Result\Layout
 * @since 2.0.0
 */
interface ViewInterface
{
    /**
     * Load layout updates
     *
     * @return ViewInterface
     * @since 2.0.0
     */
    public function loadLayoutUpdates();

    /**
     * Rendering layout
     *
     * @param   string $output
     * @return  ViewInterface
     * @since 2.0.0
     */
    public function renderLayout($output = '');

    /**
     * Retrieve the default layout handle name for the current action
     *
     * @return string
     * @since 2.0.0
     */
    public function getDefaultLayoutHandle();

    /**
     * Load layout by handles(s)
     *
     * @param   string|null|bool $handles
     * @param   bool $generateBlocks
     * @param   bool $generateXml
     * @param   bool $addActionHandles
     * @return  ViewInterface
     * @throws  \RuntimeException
     * @since 2.0.0
     */
    public function loadLayout($handles = null, $generateBlocks = true, $generateXml = true, $addActionHandles = true);

    /**
     * Generate layout xml
     *
     * @return ViewInterface
     * @since 2.0.0
     */
    public function generateLayoutXml();

    /**
     * Add layout updates handles associated with the action page
     *
     * @param array $parameters page parameters
     * @param string $defaultHandle
     * @return bool
     * @since 2.0.0
     */
    public function addPageLayoutHandles(array $parameters = [], $defaultHandle = null);

    /**
     * Generate layout blocks
     *
     * @return ViewInterface
     * @since 2.0.0
     */
    public function generateLayoutBlocks();

    /**
     * Retrieve current page object
     *
     * @return \Magento\Framework\View\Result\Page
     * @since 2.0.0
     */
    public function getPage();

    /**
     * Retrieve current layout object
     *
     * @return \Magento\Framework\View\LayoutInterface
     * @since 2.0.0
     */
    public function getLayout();

    /**
     * Add layout handle by full controller action name
     *
     * @return ViewInterface
     * @since 2.0.0
     */
    public function addActionLayoutHandles();

    /**
     * Set isLayoutLoaded flag
     *
     * @param bool $value
     * @return void
     * @since 2.0.0
     */
    public function setIsLayoutLoaded($value);

    /**
     * Returns is layout loaded
     *
     * @return bool
     * @since 2.0.0
     */
    public function isLayoutLoaded();
}
