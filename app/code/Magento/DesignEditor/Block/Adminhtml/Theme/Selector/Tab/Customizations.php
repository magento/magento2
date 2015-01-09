<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\DesignEditor\Block\Adminhtml\Theme\Selector\Tab;

/**
 * Theme selector tab for customized themes
 */
class Customizations extends \Magento\DesignEditor\Block\Adminhtml\Theme\Selector\Tab\AbstractTab
{
    /**
     * Initialize tab block
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setActive(true);
    }

    /**
     * Return Tab title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('My Customizations');
    }
}
