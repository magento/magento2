<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\DesignEditor\Block\Adminhtml\Editor\Tools\Tabs;

/**
 * Block that renders tabs
 *
 * @method bool getIsActive()
 */
abstract class AbstractTabs extends \Magento\Framework\View\Element\Template
{
    /**
     * Alias of tab handle block in layout
     */
    const TAB_HANDLE_BLOCK_ALIAS = 'tab_handle';

    /**
     * Alias of tab body block in layout
     */
    const TAB_BODY_BLOCK_ALIAS = 'tab_body';

    /**
     * @var string Tab HTML identifier
     */
    protected $_htmlId;

    /**
     * @var string Tab HTML title
     */
    protected $_title;

    /**
     * Get HTML identifier
     *
     * @return string
     */
    public function getHtmlId()
    {
        return $this->_htmlId;
    }

    /**
     * Get translated title
     *
     * @return string
     */
    public function getTitle()
    {
        return __($this->_title);
    }

    /**
     * Get tabs html
     *
     * @return string[]
     */
    public function getTabContents()
    {
        $contents = [];
        /** @var $tabBodyBlock \Magento\DesignEditor\Block\Adminhtml\Editor\Tools\Tabs\Body */
        $tabBodyBlock = $this->getChildBlock(self::TAB_BODY_BLOCK_ALIAS);
        foreach ($this->getTabs() as $tab) {
            $contents[] = $tabBodyBlock->setContentBlock(
                $tab['content_block']
            )->setIsActive(
                $tab['is_active']
            )->setTabId(
                $tab['id']
            )->toHtml();
        }
        return $contents;
    }

    /**
     * Get tabs handles
     *
     * @return string[]
     */
    public function getTabHandles()
    {
        /** @var $tabHandleBlock \Magento\Backend\Block\Template */
        $tabHandleBlock = $this->getChildBlock(self::TAB_HANDLE_BLOCK_ALIAS);
        $handles = [];
        foreach ($this->getTabs() as $tab) {
            $href = '#' . $tab['id'];
            $handles[] = $tabHandleBlock->setIsActive(
                $tab['is_active']
            )->setHref(
                $href
            )->setTitle(
                $tab['title']
            )->toHtml();
        }

        return $handles;
    }

    /**
     * Get tabs data
     *
     * @return array
     */
    abstract public function getTabs();
}
