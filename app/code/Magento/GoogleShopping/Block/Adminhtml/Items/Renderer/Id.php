<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Adminhtml Google Shopping Item Id Renderer
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\GoogleShopping\Block\Adminhtml\Items\Renderer;

class Id extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * Renders Google Shopping Item Id
     *
     * @param   \Magento\Framework\Object $row
     * @return  string
     */
    public function render(\Magento\Framework\Object $row)
    {
        $baseUrl = 'http://www.google.com/merchants/view?docId=';

        $itemUrl = $row->getData($this->getColumn()->getIndex());
        $urlParts = parse_url($itemUrl);
        if (isset($urlParts['path'])) {
            $pathParts = explode('/', $urlParts['path']);
            $itemId = $pathParts[count($pathParts) - 1];
        } else {
            $itemId = $itemUrl;
        }
        $title = __('View Item in Google Content');

        return sprintf(
            '<a href="%s" alt="%s" title="%s" target="_blank">%s</a>',
            $baseUrl . $itemId,
            $title,
            $title,
            $itemId
        );
    }
}
