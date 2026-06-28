<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */

/**
 * Newsletter subscribers grid checkbox item renderer
 */
namespace Magento\Newsletter\Block\Adminhtml\Subscriber\Grid\Renderer;

class Checkbox extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * Renders grid column
     *
     * @param   \Magento\Framework\DataObject $row
     * @return  string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        if ($row->getSubscriberStatus() == \Magento\Newsletter\Model\Subscriber::STATUS_SUBSCRIBED) {
            return '<input type="checkbox" name="subscriber[]" value="' .
                $row->getId() .
                '" class="subscriberCheckbox"/>';
        } else {
            return '';
        }
    }
}
