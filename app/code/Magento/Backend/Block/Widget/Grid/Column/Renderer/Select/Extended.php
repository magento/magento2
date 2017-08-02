<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Widget\Grid\Column\Renderer\Select;

/**
 * Class \Magento\Backend\Block\Widget\Grid\Column\Renderer\Select\Extended
 *
 * @since 2.0.0
 */
class Extended extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Select
{
    /**
     * Prepare data for renderer
     *
     * @return array
     * @since 2.0.0
     */
    protected function _getOptions()
    {
        return $this->getColumn()->getOptions();
    }
}
