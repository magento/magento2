<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
