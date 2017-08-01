<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Product Child tab
 */
namespace Magento\Catalog\Block\Adminhtml\Product\Edit\Tab;

/**
 * Class \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\ChildTab
 *
 * @since 2.0.0
 */
class ChildTab extends \Magento\Backend\Block\Template
{
    /**
     * @var \Magento\Backend\Block\Widget\Tab\TabInterface
     * @since 2.0.0
     */
    protected $tab;

    /**
     * @param \Magento\Backend\Block\Widget\Tab\TabInterface $tab
     * @return $this
     * @since 2.0.0
     */
    public function setTab(\Magento\Backend\Block\Widget\Tab\TabInterface $tab)
    {
        $this->tab = $tab;
        return $this;
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getTitle()
    {
        return $this->tab->getTabTitle();
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getContent()
    {
        return $this->tab->toHtml();
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getTabId()
    {
        return $this->tab->getTabId();
    }

    /**
     * @return bool
     * @since 2.0.0
     */
    public function isTabOpened()
    {
        return (bool)$this->tab->getData('opened');
    }
}
