<?php
/**
 * Phrase renderer interface
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Phrase;

/**
 * Translated phrase renderer
 *
 * @api
 * @since 2.0.0
 */
interface RendererInterface
{
    /**
     * Render source text
     *
     * @param [] $source
     * @param [] $arguments
     * @return string
     * @since 2.0.0
     */
    public function render(array $source, array $arguments);
}
