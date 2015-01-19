<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Theme selector tab for available themes
 */
namespace Magento\DesignEditor\Block\Adminhtml\Theme\Selector\Tab;

class Available extends \Magento\DesignEditor\Block\Adminhtml\Theme\Selector\Tab\AbstractTab
{
    /**
     * Return tab content, available theme list
     *
     * @return string
     */
    protected function _toHtml()
    {
        $this->getChildBlock('available.theme.list')->setTabId($this->getId());
        return $this->getChildHtml('available.theme.list');
    }

    /**
     * Return Tab title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Available Themes');
    }
}
