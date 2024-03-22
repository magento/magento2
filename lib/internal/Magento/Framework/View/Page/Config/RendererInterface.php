<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Page\Config;

/**
 * Interface RendererInterface
 *
 * @api
 */
interface RendererInterface
{
    /**
     * Render Element Attributes
     *
     * @param string $elementType
     * @return string
     */
    public function renderElementAttributes($elementType);

    /**
     * Render Head Content
     *
     * @return string
     */
    public function renderHeadContent();

    /**
     * Render Title
     *
     * @return string
     */
    public function renderTitle();

    /**
     * Render Metadata
     *
     * @return string
     */
    public function renderMetadata();

    /**
     * Prepare Favicon
     *
     * @return void
     */
    public function prepareFavicon();

    /**
     * Returns rendered HTML for all Assets (CSS before)
     *
     * @param array $resultGroups
     *
     * @return string
     */
    public function renderAssets($resultGroups = []);
}
