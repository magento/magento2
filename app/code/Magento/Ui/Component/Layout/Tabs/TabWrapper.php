<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Layout\Tabs;

use Magento\Framework\View\Element\Text\ListText;

/**
 * Class TabWrapper
 */
class TabWrapper extends ListText implements TabInterface
{
    /**
     * @var bool
     */
    protected $canShowTab = true;

    /**
     * @var bool
     */
    protected $isHidden = false;

    /**
     * @var bool
     */
    protected $isAjaxLoaded = false;

    /**
     * Return Tab label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return (string) $this->getData('tab_label');
    }

    /**
     * Return Tab title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return (string) $this->getTabLabel();
    }

    /**
     * Tab class getter
     *
     * @return string
     */
    public function getTabClass()
    {
        return (string) $this->getData('tab_class');
    }

    /**
     * Return URL link to Tab content
     *
     * @return string
     */
    public function getTabUrl()
    {
        return (string) $this->getData('tab_url');
    }

    /**
     * Tab should be loaded trough Ajax call
     *
     * @return bool
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
     */
    public function isHidden()
    {
        $flag = $this->getData('is_hidden');
        return $flag !== null ? (bool) $flag : $this->isHidden;
    }
}
