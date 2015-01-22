<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\DesignEditor\Block\Adminhtml\Editor\Tools\QuickStyles;

/**
 * Block that renders Quick Styles > Backgrounds tab
 *
 * @method \Magento\Framework\View\Design\ThemeInterface getTheme()
 * @method setTheme($theme)
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Backgrounds extends \Magento\DesignEditor\Block\Adminhtml\Editor\Tools\QuickStyles\AbstractTab
{
    /**
     * Tab form HTML identifier
     *
     * @var string
     */
    protected $_formId = 'quick-styles-form-backgrounds';

    /**
     * Controls group which will be rendered on the tab form
     *
     * @var string
     */
    protected $_tab = 'backgrounds';
}
