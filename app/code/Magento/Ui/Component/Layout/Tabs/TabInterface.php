<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Layout\Tabs;

/**
 * Interface TabInterface
 * @since 2.0.0
 */
interface TabInterface
{
    /**
     * Return Tab label
     *
     * @return string
     * @since 2.0.0
     */
    public function getTabLabel();

    /**
     * Return Tab title
     *
     * @return string
     * @since 2.0.0
     */
    public function getTabTitle();

    /**
     * Tab class getter
     *
     * @return string
     * @since 2.0.0
     */
    public function getTabClass();

    /**
     * Return URL link to Tab content
     *
     * @return string
     * @since 2.0.0
     */
    public function getTabUrl();

    /**
     * Tab should be loaded trough Ajax call
     *
     * @return bool
     * @since 2.0.0
     */
    public function isAjaxLoaded();

    /**
     * Can show tab in tabs
     *
     * @return boolean
     * @since 2.0.0
     */
    public function canShowTab();

    /**
     * Tab is hidden
     *
     * @return boolean
     * @since 2.0.0
     */
    public function isHidden();

    /**
     * Retrieve Tab content
     *
     * @return string
     * @since 2.0.0
     */
    public function toHtml();
}
