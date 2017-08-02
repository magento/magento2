<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Catalog\Product\Tab;

/**
 * @api
 * @since 2.0.0
 */
class Container extends \Magento\Backend\Block\Template implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * Return Tab label
     *
     * @return string
     * @since 2.0.0
     */
    public function getTabLabel()
    {
        return '';
    }

    /**
     * Return Tab title
     *
     * @return string
     * @since 2.0.0
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     * @since 2.0.0
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     * @since 2.0.0
     */
    public function isHidden()
    {
        return false;
    }
}
