<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Widget\Grid\Column\Renderer\Checkboxes;

class Extended extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Checkbox
{
    /**
     * Prepare data for renderer
     *
     * @return array
     */
    public function _getValues()
    {
        return $this->getColumn()->getValues();
    }
}
