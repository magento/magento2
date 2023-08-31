<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Widget\Tab;

/**
 * Backend Widget Tab Interface
 *
 * @api
 * @since 100.0.2
 */
interface TabInterface
{
    /**
     * Return Tab label
     *
     * @return string
     */
    public function getTabLabel();

    /**
     * Return Tab title
     *
     * @return string
     */
    public function getTabTitle();

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab();

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden();
}
