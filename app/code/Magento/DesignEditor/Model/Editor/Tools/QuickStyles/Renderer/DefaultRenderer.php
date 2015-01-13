<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\DesignEditor\Model\Editor\Tools\QuickStyles\Renderer;

/**
 * Default css renderer
 */
class DefaultRenderer extends \Magento\DesignEditor\Model\Editor\Tools\QuickStyles\Renderer\AbstractRenderer
{
    /**
     * Render concrete element
     *
     * Return format:
     * .header #title { color: red; }
     *
     * @param array $data
     * @return string
     */
    protected function _render($data)
    {
        return "{$data['selector']} { {$data['attribute']}: {$data['value']}; }";
    }
}
