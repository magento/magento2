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
 * @author     Magento Core Team <core@magentocommerce.com>
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
     */
    public function setColumn($column);

    /**
     * Returns row associated with the renderer
     *
     * @abstract
     * @return void
     * @api
     */
    public function getColumn();

    /**
     * Renders grid column
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     * @api
     */
    public function render(\Magento\Framework\DataObject $row);
}
