<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
