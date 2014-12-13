<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
