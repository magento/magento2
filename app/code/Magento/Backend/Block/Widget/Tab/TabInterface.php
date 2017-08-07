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
 */
interface TabInterface
{
    /**
     * Return Tab label
     *
     * @return string
     * @api
     */
    public function getTabLabel();

    /**
     * Return Tab title
     *
     * @return string
     * @api
     */
    public function getTabTitle();

    /**
     * Can show tab in tabs
     *
     * @return boolean
     * @api
     */
    public function canShowTab();

    /**
     * Tab is hidden
     *
     * @return boolean
     * @api
     */
    public function isHidden();
}
