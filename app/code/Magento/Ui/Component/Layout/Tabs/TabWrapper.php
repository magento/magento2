<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Layout\Tabs;

use Magento\Framework\View\Element\Text\ListText;

/**
 * Class TabWrapper
 * @since 2.0.0
 */
class TabWrapper extends ListText implements TabInterface
{
    /**
     * @var bool
     * @since 2.0.0
     */
    protected $canShowTab = true;

    /**
     * @var bool
     * @since 2.0.0
     */
    protected $isHidden = false;

    /**
     * @var bool
     * @since 2.0.0
     */
    protected $isAjaxLoaded = false;

    /**
     * Return Tab label
     *
     * @return string
     * @since 2.0.0
     */
    public function getTabLabel()
    {
        return (string) $this->getData('tab_label');
    }

    /**
     * Return Tab title
     *
     * @return string
     * @since 2.0.0
     */
    public function getTabTitle()
    {
        return (string) $this->getTabLabel();
    }

    /**
     * Tab class getter
     *
     * @return string
     * @since 2.0.0
     */
    public function getTabClass()
    {
        return (string) $this->getData('tab_class');
    }

    /**
     * Return URL link to Tab content
     *
     * @return string
     * @since 2.0.0
     */
    public function getTabUrl()
    {
        return (string) $this->getData('tab_url');
    }

    /**
     * Tab should be loaded trough Ajax call
     *
     * @return bool
     * @since 2.0.0
     */
    public function isAjaxLoaded()
    {
        $flag = $this->getData('is_ajax_loaded');
        return $flag !== null ? (bool) $flag : $this->isAjaxLoaded;
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     * @since 2.0.0
     */
    public function canShowTab()
    {
        $flag = $this->getData('can_show_tab');
        return $flag !== null ? (bool) $flag : $this->canShowTab;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     * @since 2.0.0
     */
    public function isHidden()
    {
        $flag = $this->getData('is_hidden');
        return $flag !== null ? (bool) $flag : $this->isHidden;
    }
}
