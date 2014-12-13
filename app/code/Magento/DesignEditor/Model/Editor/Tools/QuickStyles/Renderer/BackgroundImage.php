<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\DesignEditor\Model\Editor\Tools\QuickStyles\Renderer;

/**
 * Background image renderer
 */
class BackgroundImage extends \Magento\DesignEditor\Model\Editor\Tools\QuickStyles\Renderer\AbstractRenderer
{
    /**
     * Render color picker attribute
     *
     * Return format:
     * .content { background-image: url(path/image.png); }
     *
     * @param array $data
     * @return string
     */
    protected function _render($data)
    {
        $override = "none";

        if (!empty($data['value'])) {
            $override = "url('{$data['value']}')";
        }

        return "{$data['selector']} { {$data['attribute']}: " . $override . "; }";
    }
}
