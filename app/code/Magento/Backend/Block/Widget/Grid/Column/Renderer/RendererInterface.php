<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Widget\Grid\Column\Renderer;

use Magento\Backend\Block\Widget\Grid\Column;

/**
 * Backend grid item renderer interface
 *
 * @api
 * @deprecated 2.2.0 in favour of UI component implementation
 * @since 2.0.0
 */
interface RendererInterface
{
    /**
     * Set column for renderer
     *
     * @param Column $column
     * @return void
     * @abstract
     * @api
     * @since 2.0.0
     */
    public function setColumn($column);

    /**
     * Returns row associated with the renderer
     *
     * @abstract
     * @return void
     * @api
     * @since 2.0.0
     */
    public function getColumn();

    /**
     * Renders grid column
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     * @api
     * @since 2.0.0
     */
    public function render(\Magento\Framework\DataObject $row);
}
