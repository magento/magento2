<?php
/**
 * Phrase renderer interface
 *
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Phrase;

/**
 * Translated phrase renderer
 *
 * @api
 * @since 100.0.2
 */
interface RendererInterface
{
    /**
     * Render source text
     *
     * @param [] $source
     * @param [] $arguments
     * @return string
     */
    public function render(array $source, array $arguments);
}
