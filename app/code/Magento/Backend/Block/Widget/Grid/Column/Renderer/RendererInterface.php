<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
     */
    public function setColumn($column);

    /**
     * Returns row associated with the renderer
     *
     * @abstract
     * @return void
     */
    public function getColumn();

    /**
     * Renders grid column
     *
     * @param \Magento\Framework\Object $row
     * @return string
     */
    public function render(\Magento\Framework\Object $row);
}
