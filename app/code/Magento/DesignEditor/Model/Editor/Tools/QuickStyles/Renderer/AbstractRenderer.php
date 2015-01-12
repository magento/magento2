<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\DesignEditor\Model\Editor\Tools\QuickStyles\Renderer;

/**
 * Quick style abstract renderer
 */
abstract class AbstractRenderer
{
    /**
     * Render CSS
     *
     * @param array $data
     * @return string
     */
    public function toCss($data)
    {
        return $this->_render($data);
    }

    /**
     * Render concrete element
     *
     * @param array $data
     * @return string
     */
    abstract protected function _render($data);
}
