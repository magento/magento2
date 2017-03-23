<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Block\Adminhtml\Edit\Tab\Wishlist\Grid\Renderer;

/**
 * Adminhtml customers wishlist grid item renderer for item visibility
 */
class Description extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * Render the description of given row.
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        return nl2br(htmlspecialchars($row->getData($this->getColumn()->getIndex())));
    }
}
