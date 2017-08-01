<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Widget\Grid\Column\Renderer\Checkboxes;

/**
 * Class \Magento\Backend\Block\Widget\Grid\Column\Renderer\Checkboxes\Extended
 *
 * @since 2.0.0
 */
class Extended extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Checkbox
{
    /**
     * Prepare data for renderer
     *
     * @return array
     * @since 2.0.0
     */
    public function _getValues()
    {
        return $this->getColumn()->getValues();
    }
}
