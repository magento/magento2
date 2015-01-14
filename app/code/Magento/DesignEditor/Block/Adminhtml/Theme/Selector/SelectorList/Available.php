<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\DesignEditor\Block\Adminhtml\Theme\Selector\SelectorList;

/**
 * Available theme list
 *
 * @method int getNextPage()
 * @method \Magento\DesignEditor\Block\Adminhtml\Theme\Selector\SelectorList\Available setNextPage(int $page)
 */
class Available extends \Magento\DesignEditor\Block\Adminhtml\Theme\Selector\SelectorList\AbstractSelectorList
{
    /**
     * Get tab title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Available Themes');
    }

    /**
     * Get next page url
     *
     * @return string
     */
    public function getNextPageUrl()
    {
        return $this->getNextPage() <= $this->getCollection()->getLastPageNumber() ? $this->getUrl(
            'adminhtml/*/*',
            ['page' => $this->getNextPage()]
        ) : '';
    }

    /**
     * Get edit button
     *
     * @param \Magento\DesignEditor\Block\Adminhtml\Theme $themeBlock
     * @return void
     */
    protected function _addEditButtonHtml($themeBlock)
    {
        $themeId = $themeBlock->getTheme()->getId();

        /** @var $assignButton \Magento\Backend\Block\Widget\Button */
        $assignButton = $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button');
        $assignButton->setData(
            [
                'label' => __('Edit'),
                'data_attribute' => [
                    'mage-init' => [
                        'button' => [
                            'event' => 'themeEdit',
                            'target' => 'body',
                            'eventData' => ['theme_id' => $themeId],
                        ],
                    ],
                ],
                'class' => 'action-edit',
            ]
        );

        $themeBlock->addButton($assignButton);
    }

    /**
     * Add theme buttons
     *
     * @param \Magento\DesignEditor\Block\Adminhtml\Theme $themeBlock
     * @return $this
     */
    protected function _addThemeButtons($themeBlock)
    {
        parent::_addThemeButtons($themeBlock);
        $this->_addAssignButtonHtml($themeBlock);
        $this->_addEditButtonHtml($themeBlock);
        return $this;
    }
}
