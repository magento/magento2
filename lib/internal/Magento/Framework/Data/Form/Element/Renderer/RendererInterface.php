<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */

/**
 * Form field renderer interface
 */
namespace Magento\Framework\Data\Form\Element\Renderer;

/**
 * @api
 * @since 100.0.2
 */
interface RendererInterface
{
    /**
     * Render form element as HTML
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element);
}
