<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Widget\Grid\Column\Renderer\Radio;

class Extended extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Radio
{
    /**
     * Prepare data for renderer
     *
     * @return array
     */
    protected function _getValues()
    {
        return $this->getColumn()->getValues();
    }
}
