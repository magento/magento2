<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Widget;

use Magento\Backend\Block\Widget\Tab\TabInterface;

/**
 * @api
 * @since 2.0.0
 */
class Tab extends \Magento\Backend\Block\Template implements TabInterface
{
    /**
     * Return Tab label
     *
     * @return string
     * @since 2.0.0
     */
    public function getTabLabel()
    {
        return $this->getLabel();
    }

    /**
     * Return Tab title
     *
     * @return string
     * @since 2.0.0
     */
    public function getTabTitle()
    {
        return $this->getTitle();
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     * @since 2.0.0
     */
    public function canShowTab()
    {
        return $this->hasCanShow() ? (bool)$this->getCanShow() : true;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     * @since 2.0.0
     */
    public function isHidden()
    {
        return $this->hasIsHidden() ? (bool)$this->getIsHidden() : false;
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getTabClass()
    {
        return $this->getClass();
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getTabUrl()
    {
        return $this->hasData('url') ? $this->getData('url') : '#';
    }
}
