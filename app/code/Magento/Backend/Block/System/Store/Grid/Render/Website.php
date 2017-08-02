<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\System\Store\Grid\Render;

/**
 * Store render website
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Website extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        return '<a title="' . __(
            'Edit Web Site'
        ) . '"
            href="' .
        $this->getUrl('adminhtml/*/editWebsite', ['website_id' => $row->getWebsiteId()]) .
        '">' .
        $this->escapeHtml($row->getData($this->getColumn()->getIndex())) .
        '</a>';
    }
}
