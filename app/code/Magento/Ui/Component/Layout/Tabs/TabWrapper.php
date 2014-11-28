<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


namespace Magento\Ui\Component\Layout\Tabs;

use Magento\Framework\View\Element\Text\ListText;

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
        return !is_null($flag) ? (bool) $flag : $this->isAjaxLoaded;
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        $flag = $this->getData('can_show_tab');
        return !is_null($flag) ? (bool) $flag : $this->canShowTab;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        $flag = $this->getData('is_hidden');
        return !is_null($flag) ? (bool) $flag : $this->isHidden;
    }
}
