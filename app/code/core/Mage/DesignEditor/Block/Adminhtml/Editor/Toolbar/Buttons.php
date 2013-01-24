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
 * VDE buttons block
 */
class Mage_DesignEditor_Block_Adminhtml_Editor_Toolbar_Buttons
    extends Mage_DesignEditor_Block_Adminhtml_Editor_Toolbar_BlockAbstract
{
    /**
     * Current theme used for preview
     *
     * @var int
     */
    protected $_themeId;

    /**
     * Get current theme id
     *
     * @return int
     */
    public function getThemeId()
    {
        return $this->_themeId;
    }

    /**
     * Get current theme id
     *
     * @param int $themeId
     * @return Mage_DesignEditor_Block_Adminhtml_Editor_Toolbar_Buttons
     */
    public function setThemeId($themeId)
    {
        $this->_themeId = $themeId;

        return $this;
    }

    /**
     * Get "View Layout" button URL
     *
     * @return string
     */
    public function getViewLayoutUrl()
    {
        return $this->getUrl('*/*/getLayoutUpdate');
    }

    /**
     * Get "Quit" button URL
     *
     * @return string
     */
    public function getQuitUrl()
    {
        return $this->getUrl('*/*/quit');
    }

    /**
     * Get "Navigation Mode" button URL
     *
     * @return string
     */
    public function getNavigationModeUrl()
    {
        return $this->getUrl('*/*/launch', array(
            'theme_id' => $this->getThemeId(),
            'mode' => Mage_DesignEditor_Model_State::MODE_NAVIGATION
        ));
    }

    /**
     * Get "Design Mode" button URL
     *
     * @return string
     */
    public function getDesignModeUrl()
    {
        return $this->getUrl('*/*/launch', array(
            'theme_id' => $this->getThemeId(),
            'mode' => Mage_DesignEditor_Model_State::MODE_DESIGN
        ));
    }

    /**
     * Get assign to storeview button
     *
     * @return string
     */
    public function getAssignButtonHtml()
    {
        /** @var $assignButton Mage_Backend_Block_Widget_Button */
        $assignButton = $this->getLayout()->createBlock('Mage_Backend_Block_Widget_Button');
        $assignButton->setData(array(
            'label'  => $this->__('Assign this Theme'),
            'data_attribute' => array(
                'mage-init' => array(
                    'button' => array(
                        'event'     => 'assign',
                        'target'    => 'body',
                        'eventData' => array(
                            'theme_id' => $this->getThemeId()
                        )
                    ),
                ),
            ),
            'class'  => 'save action-theme-assign',
            'target' => '_blank'
        ));

        return $assignButton->toHtml();
    }

    /**
     * Get switch mode button
     *
     * @return string
     */
    public function getSwitchModeButtonHtml()
    {
        $eventData = array(
            'theme_id' => $this->getThemeId(),
        );

        if ($this->isNavigationMode()) {
            $label                 = $this->__('Design Mode');
            $eventData['mode_url'] = $this->getDesignModeUrl();
        } else {
            $label                         = $this->__('Navigation Mode');
            $eventData['mode_url']         = $this->getNavigationModeUrl();
            $eventData['save_changes_url'] = $this->getSaveTemporaryLayoutUpdateUrl();
        }

        /** @var $switchButton Mage_Backend_Block_Widget_Button */
        $switchButton = $this->getLayout()->createBlock('Mage_Backend_Block_Widget_Button');
        $switchButton->setData(array(
            'label' => $label,
            'data_attribute' => array(
                'mage-init' => array(
                    'button' => array(
                        'event'     => 'switchMode',
                        'target'    => 'body',
                        'eventData' => $eventData
                    ),
                ),
            ),
            'class' => 'action-switch-mode',
        ));

        return $switchButton->toHtml();
    }

    /**
     * Get save temporary layout changes url
     *
     * @return string
     */
    public function getSaveTemporaryLayoutUpdateUrl()
    {
        return $this->getUrl('*/*/saveTemporaryLayoutUpdate');
    }
}
