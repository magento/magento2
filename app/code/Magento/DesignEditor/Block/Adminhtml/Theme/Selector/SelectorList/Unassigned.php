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

/**
 * Unassigned theme list
 */
namespace Magento\DesignEditor\Block\Adminhtml\Theme\Selector\SelectorList;

class Unassigned extends \Magento\DesignEditor\Block\Adminhtml\Theme\Selector\SelectorList\AbstractSelectorList
{
    /**
     * Get list title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Themes Not Assigned to Store Views');
    }

    /**
     * Get remove button
     *
     * @param \Magento\DesignEditor\Block\Adminhtml\Theme $themeBlock
     * @return string
     */
    protected function _addRemoveButtonHtml($themeBlock)
    {
        $themeId = $themeBlock->getTheme()->getId();
        $themeTitle = $themeBlock->getTheme()->getThemeTitle();
        /** @var $removeButton \Magento\Backend\Block\Widget\Button */
        $removeButton = $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button');

        $removeButton->setData(
            array(
                'label' => __('Remove'),
                'data_attribute' => array(
                    'mage-init' => array(
                        'button' => array(
                            'event' => 'delete',
                            'target' => 'body',
                            'eventData' => array(
                                'url' => $this->getUrl(
                                    '*/system_design_theme/delete/',
                                    array('id' => $themeId, 'back' => true)
                                ),
                                'confirm' => array('message' => __('Are you sure you want to delete this theme?')),
                                'title' => __('Delete %1 Theme', $themeTitle)
                            )
                        )
                    )
                ),
                'class' => 'action-delete',
                'target' => '_blank'
            )
        );

        $themeBlock->addButton($removeButton);
        return $this;
    }

    /**
     * Add theme buttons
     *
     * @param \Magento\DesignEditor\Block\Adminhtml\Theme $themeBlock
     * @return \Magento\DesignEditor\Block\Adminhtml\Theme\Selector\SelectorList\AbstractSelectorList
     */
    protected function _addThemeButtons($themeBlock)
    {
        parent::_addThemeButtons($themeBlock);

        $this->_addDuplicateButtonHtml(
            $themeBlock
        )->_addAssignButtonHtml(
            $themeBlock
        )->_addEditButtonHtml(
            $themeBlock
        )->_addRemoveButtonHtml(
            $themeBlock
        );
        return $this;
    }
}
