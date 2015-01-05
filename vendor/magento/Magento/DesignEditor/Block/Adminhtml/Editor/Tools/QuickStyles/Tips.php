<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\DesignEditor\Block\Adminhtml\Editor\Tools\QuickStyles;

/**
 * Block that renders JS tab
 *
 * @method \Magento\Framework\View\Design\ThemeInterface getTheme()
 * @method setTheme($theme)
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Tips extends \Magento\DesignEditor\Block\Adminhtml\Editor\Tools\QuickStyles\AbstractTab
{
    /**
     * Tab form HTML identifier
     *
     * @var string
     */
    protected $_formId = 'quick-styles-form-tips';

    /**
     * Controls group which will be rendered on the tab form
     *
     * @var string
     */
    protected $_tab = 'tips';
}
