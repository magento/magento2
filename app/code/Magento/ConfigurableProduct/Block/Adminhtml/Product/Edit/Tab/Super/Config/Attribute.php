<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Renderer for attribute block
 */
namespace Magento\ConfigurableProduct\Block\Adminhtml\Product\Edit\Tab\Super\Config;

class Attribute extends \Magento\ConfigurableProduct\Block\Adminhtml\Product\Edit\Tab\Super\Config
{
    /**
     * Render block
     *
     * @param array $arguments
     * @return string
     */
    public function render(array $arguments)
    {
        $this->assign($arguments);
        return $this->toHtml();
    }
}
