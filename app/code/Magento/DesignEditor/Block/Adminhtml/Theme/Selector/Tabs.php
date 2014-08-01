<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
