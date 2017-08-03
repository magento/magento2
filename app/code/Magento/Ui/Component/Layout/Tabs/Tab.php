<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Layout\Tabs;

use Magento\Ui\Component\AbstractComponent;

/**
 * Class Tab
 * @since 2.0.0
 */
class Tab extends AbstractComponent implements TabInterface
{
    const NAME = 'tab';

    /**
     * Get component name
     *
     * @return string
     * @since 2.0.0
     */
    public function getComponentName()
    {
        return static::NAME;
    }

    /**
     * Return Tab label
     *
     * @return string
     * @since 2.0.0
     */
    public function getTabLabel()
    {
        return $this->getData('tab_label');
    }

    /**
     * Return Tab title
     *
     * @return string
     * @since 2.0.0
     */
    public function getTabTitle()
    {
        return $this->getData('tab_title');
    }

    /**
     * Tab class getter
     *
     * @return string
     * @since 2.0.0
     */
    public function getTabClass()
    {
        return $this->getData('tab_class');
    }

    /**
     * Return URL link to Tab content
     *
     * @return string
     * @since 2.0.0
     */
    public function getTabUrl()
    {
        return $this->getData('tab_url');
    }

    /**
     * Tab should be loaded trough Ajax call
     *
     * @return bool
     * @since 2.0.0
     */
    public function isAjaxLoaded()
    {
        return $this->getData('is_ajax_loaded');
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     * @since 2.0.0
     */
    public function canShowTab()
    {
        return $this->getData('can_show_tab');
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     * @since 2.0.0
     */
    public function isHidden()
    {
        return $this->getData('is_hidden');
    }
}
