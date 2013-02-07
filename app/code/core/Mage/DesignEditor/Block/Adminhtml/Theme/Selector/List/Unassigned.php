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
 * Unassigned theme list
 */
class Mage_DesignEditor_Block_Adminhtml_Theme_Selector_List_Unassigned
    extends Mage_DesignEditor_Block_Adminhtml_Theme_Selector_List_Abstract
{
    /**
     * Get list title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->__('Unassigned Themes');
    }

    /**
     * Get remove button
     *
     * @param Mage_DesignEditor_Block_Adminhtml_Theme $themeBlock
     * @return string
     */
    protected function _addRemoveButtonHtml($themeBlock)
    {
        $themeId = $themeBlock->getTheme()->getId();
        /** @var $removeButton Mage_Backend_Block_Widget_Button */
        $removeButton = $this->getLayout()->createBlock('Mage_Backend_Block_Widget_Button');

        $removeButton->setData(array(
            'label'     => $this->__('Remove'),
            'data_attribute'  => array(
                'mage-init' => array(
                    'button' => array(
                        'event' => 'delete',
                        'target' => 'body',
                        'eventData' => array(
                            'url' => $this->getUrl(
                                '*/system_design_theme/delete/',
                                array('id' => $themeId, 'back' => true)
                            )
                        )
                    ),
                ),
            ),
            'class'   => 'action-delete',
            'target'  => '_blank'
        ));

        $themeBlock->addButton($removeButton);
        return $this;
    }

    /**
     * Add theme buttons
     *
     * @param Mage_DesignEditor_Block_Adminhtml_Theme $themeBlock
     * @return Mage_DesignEditor_Block_Adminhtml_Theme_Selector_List_Abstract
     */
    protected function _addThemeButtons($themeBlock)
    {
        parent::_addThemeButtons($themeBlock);

        $this->_addPreviewButtonHtml($themeBlock)->_addAssignButtonHtml($themeBlock)->_addEditButtonHtml($themeBlock)
            ->_addRemoveButtonHtml($themeBlock);
        return $this;
    }
}
