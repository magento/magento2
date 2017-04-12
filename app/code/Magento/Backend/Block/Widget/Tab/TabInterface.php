<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Backend Widget Tab Interface
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Backend\Block\Widget\Tab;

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
