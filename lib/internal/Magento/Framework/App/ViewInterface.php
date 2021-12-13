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
 * @deprecated 101.0.0
 * @see \Magento\Framework\View\Result\Layout
 * @since 100.0.2
 */
interface ViewInterface
{
    /**
     * Load layout updates
     *
     * @return ViewInterface
     */
    public function loadLayoutUpdates();

    /**
     * Rendering layout
     *
     * @param   string $output
     * @return  ViewInterface
     */
    public function renderLayout($output = '');

    /**
     * Retrieve the default layout handle name for the current action
     *
     * @return string
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
     */
    public function loadLayout($handles = null, $generateBlocks = true, $generateXml = true, $addActionHandles = true);

    /**
     * Generate layout xml
     *
     * @return ViewInterface
     */
    public function generateLayoutXml();

    /**
     * Add layout updates handles associated with the action page
     *
     * @param array $parameters page parameters
     * @param string $defaultHandle
     * @return bool
     */
    public function addPageLayoutHandles(array $parameters = [], $defaultHandle = null);

    /**
     * Generate layout blocks
     *
     * @return ViewInterface
     */
    public function generateLayoutBlocks();

    /**
     * Retrieve current page object
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function getPage();

    /**
     * Retrieve current layout object
     *
     * @return \Magento\Framework\View\LayoutInterface
     */
    public function getLayout();

    /**
     * Add layout handle by full controller action name
     *
     * @return ViewInterface
     */
    public function addActionLayoutHandles();

    /**
     * Set isLayoutLoaded flag
     *
     * @param bool $value
     * @return void
     */
    public function setIsLayoutLoaded($value);

    /**
     * Returns is layout loaded
     *
     * @return bool
     */
    public function isLayoutLoaded();
}
