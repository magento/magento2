<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Ui\Component\Layout\Tabs;

/**
 * Interface TabInterface
 *
 * @api
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
     * Tab class getter
     *
     * @return string
     */
    public function getTabClass();

    /**
     * Return URL link to Tab content
     *
     * @return string
     */
    public function getTabUrl();

    /**
     * Tab should be loaded trough Ajax call
     *
     * @return bool
     */
    public function isAjaxLoaded();

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

    /**
     * Retrieve Tab content
     *
     * @return string
     */
    public function toHtml();
}
