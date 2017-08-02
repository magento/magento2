<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Form field renderer interface
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Framework\Data\Form\Element\Renderer;

/**
 * @api
 * @since 2.0.0
 */
interface RendererInterface
{
    /**
     * Render form element as HTML
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     * @since 2.0.0
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element);
}
