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
 * @since 2.0.0
 */
interface TabInterface
{
    /**
     * Return Tab label
     *
     * @return string
     * @api
     * @since 2.0.0
     */
    public function getTabLabel();

    /**
     * Return Tab title
     *
     * @return string
     * @api
     * @since 2.0.0
     */
    public function getTabTitle();

    /**
     * Can show tab in tabs
     *
     * @return boolean
     * @api
     * @since 2.0.0
     */
    public function canShowTab();

    /**
     * Tab is hidden
     *
     * @return boolean
     * @api
     * @since 2.0.0
     */
    public function isHidden();
}
