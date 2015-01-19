<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\DesignEditor\Block\Adminhtml\Theme\Selector;

/**
 * Theme selectors tabs container
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * Initialize tab
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('theme_selector_tabs');
        $this->setDestElementId('theme_selector');
        $this->setIsHoriz(true);
    }

    /**
     * Add content container to template
     *
     * @return string
     */
    protected function _toHtml()
    {
        return parent::_toHtml() .
            '<div id="' .
            $this->getDestElementId() .
            '" class="theme-selector"><div>' .
            $this->_getScript();
    }

    /**
     * Get additional script for tabs block
     *
     * @return string
     */
    protected function _getScript()
    {
        $script = sprintf(
            "require(['jquery', 'Magento_DesignEditor/js/theme-selector'], function($){
                $('.themes-customizations .theme').themeControl({url: '%s'});
            });",
            $this->getUrl('adminhtml/*/quickEdit')
        );
        return sprintf('<script type="text/javascript">%s</script>', $script);
    }
}
