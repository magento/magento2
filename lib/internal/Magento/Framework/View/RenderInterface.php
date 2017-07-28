<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View;

/**
 * Interface RenderInterface
 *
 * @api
 * @since 2.0.0
 */
interface RenderInterface
{
    /**
     * Render template
     *
     * @param string $template
     * @param array $data
     * @return string
     * @since 2.0.0
     */
    public function renderTemplate($template, array $data);

    /**
     * Render container
     *
     * @param string $content
     * @param array $containerInfo
     * @return string
     * @since 2.0.0
     */
    public function renderContainer($content, array $containerInfo = []);
}
