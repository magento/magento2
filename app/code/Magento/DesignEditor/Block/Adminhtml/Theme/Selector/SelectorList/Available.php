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
            array('page' => $this->getNextPage())
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
            array(
                'label' => __('Edit'),
                'data_attribute' => array(
                    'mage-init' => array(
                        'button' => array(
                            'event' => 'themeEdit',
                            'target' => 'body',
                            'eventData' => array('theme_id' => $themeId)
                        )
                    )
                ),
                'class' => 'action-edit'
            )
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
