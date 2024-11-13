<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\System\Store\Grid\Render;

use Magento\Framework\DataObject;

/**
 * Store render group
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Group extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @inheritDoc
     */
    public function render(DataObject $row)
    {
        if (!$row->getData($this->getColumn()->getIndex())) {
            return null;
        }
        return '<a title="' . __(
            'Edit Store'
        ) . '"
            href="' .
        $this->getUrl('adminhtml/*/editGroup', ['group_id' => $row->getGroupId()]) .
        '">' .
        $this->escapeHtml($row->getData($this->getColumn()->getIndex())) .
        '</a><br />'
        . '(' . __('Code') . ': ' . $this->escapeHtml($row->getGroupCode()) . ')';
    }
}
