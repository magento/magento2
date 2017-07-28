<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Widget\Grid\Column\Renderer\Radio;

/**
 * Class \Magento\Backend\Block\Widget\Grid\Column\Renderer\Radio\Extended
 *
 * @since 2.0.0
 */
class Extended extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Radio
{
    /**
     * Prepare data for renderer
     *
     * @return array
     * @since 2.0.0
     */
    protected function _getValues()
    {
        return $this->getColumn()->getValues();
    }
}
