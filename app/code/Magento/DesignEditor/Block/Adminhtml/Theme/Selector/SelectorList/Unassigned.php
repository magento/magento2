<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
            [
                'label' => __('Remove'),
                'data_attribute' => [
                    'mage-init' => [
                        'button' => [
                            'event' => 'delete',
                            'target' => 'body',
                            'eventData' => [
                                'url' => $this->getUrl(
                                    '*/system_design_theme/delete/',
                                    ['id' => $themeId, 'back' => true]
                                ),
                                'confirm' => ['message' => __('Are you sure you want to delete this theme?')],
                                'title' => __('Delete %1 Theme', $themeTitle),
                            ],
                        ],
                    ],
                ],
                'class' => 'action-delete',
                'target' => '_blank',
            ]
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
