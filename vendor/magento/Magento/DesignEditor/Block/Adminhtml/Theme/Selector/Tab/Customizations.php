<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
