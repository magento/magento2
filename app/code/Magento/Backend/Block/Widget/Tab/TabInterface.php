<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
