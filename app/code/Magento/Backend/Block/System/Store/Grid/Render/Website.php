<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Backend\Block\System\Store\Grid\Render;

/**
 * Store render website
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Website extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * {@inheritdoc}
     */
    public function render(\Magento\Framework\Object $row)
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
