<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Page\Config;

/**
 * Interface RendererInterface
 *
 * @package Magento\Framework\View\Page\Config
 * @since 2.0.0
 */
interface RendererInterface
{
    /**
     * Render Element Attributes
     *
     * @param string $elementType
     *
     * @return string
     * @since 2.0.0
     */
    public function renderElementAttributes($elementType);

    /**
     * Render Head Content
     *
     * @return string
     * @since 2.0.0
     */
    public function renderHeadContent();

    /**
     * Render Title
     *
     * @return string
     * @since 2.0.0
     */
    public function renderTitle();

    /**
     * Render Metadata
     *
     * @return string
     * @since 2.0.0
     */
    public function renderMetadata();

    /**
     * Prepare Favicon
     *
     * @return void
     * @since 2.0.0
     */
    public function prepareFavicon();

    /**
     * Returns rendered HTML for all Assets (CSS before)
     *
     * @param array $resultGroups
     *
     * @return string
     * @since 2.0.0
     */
    public function renderAssets($resultGroups = []);
}
