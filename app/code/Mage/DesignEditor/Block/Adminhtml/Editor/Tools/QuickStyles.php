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
 * @category    Mage
 * @package     Mage_DesignEditor
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Block that renders Design tab
 */
class Mage_DesignEditor_Block_Adminhtml_Editor_Tools_QuickStyles
    extends Mage_DesignEditor_Block_Adminhtml_Editor_Tools_Tabs_Abstract
{
    /**
     * Tab HTML identifier
     */
    protected $_htmlId = 'vde-tab-quick-styles';

    /**
     * Tab HTML title
     */
    protected $_title = 'Quick Styles';

    /**
     * Get tabs data
     *
     * @return array
     */
    public function getTabs()
    {
        return array(
            array(
                'is_active'     => true,
                'id'          => 'vde-tab-header',
                'title'         => strtoupper($this->__('Header')),
                'content_block' => 'design_editor_tools_quick-styles_header'
            ),
            array(
                'is_active'     => false,
                'id'          => 'vde-tab-bgs',
                'title'         => strtoupper($this->__('Backgrounds')),
                'content_block' => 'design_editor_tools_quick-styles_backgrounds'
            ),
            array(
                'is_active'     => false,
                'id'          => 'vde-tab-buttons',
                'title'         => strtoupper($this->__('Buttons & Icons')),
                'content_block' => 'design_editor_tools_quick-styles_buttons'
            ),
            array(
                'is_active'     => false,
                'id'          => 'vde-tab-tips',
                'title'         => strtoupper($this->__('Tips & Messages')),
                'content_block' => 'design_editor_tools_quick-styles_tips'
            ),
            array(
                'is_active'     => false,
                'id'          => 'vde-tab-fonts',
                'title'         => strtoupper($this->__('Fonts')),
                'content_block' => 'design_editor_tools_quick-styles_fonts'
            ),

        );
    }

    /**
     * Get the tab state
     *
     * Active tab is showed, while inactive tabs are hidden
     *
     * @return bool
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsActive()
    {
        return true;
    }
}
