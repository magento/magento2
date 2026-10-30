<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Widget\Grid\Column\Renderer\Select;

class Extended extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Select
{
    /**
     * Prepare data for renderer
     *
     * @return array
     */
    protected function _getOptions()
    {
        return $this->getColumn()->getOptions();
    }
}
