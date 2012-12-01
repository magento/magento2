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
 * @category    Mage
 * @package     Mage_Backend
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Tabs block
 *
 * @category   Mage
 * @package    Mage_Backend
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Backend_Block_Widget_Tabs extends Mage_Backend_Block_Widget
{
    /**
     * tabs structure
     *
     * @var array
     */
    protected $_tabs = array();

    /**
     * Active tab key
     *
     * @var string
     */
    protected $_activeTab = null;

    /**
     * Destination HTML element id
     *
     * @var string
     */
    protected $_destElementId = 'content';

    protected $_template = 'Mage_Backend::widget/tabs.phtml';

    /**
     * retrieve destination html element id
     *
     * @return string
     */
    public function getDestElementId()
    {
        return $this->_destElementId;
    }

    public function setDestElementId($elementId)
    {
        $this->_destElementId = $elementId;
        return $this;
    }

    /**
     * Add new tab after another
     *
     * @param   string $tabId new tab Id
     * @param   array|Varien_Object $tab
     * @param   string $afterTabId
     * @return  Mage_Backend_Block_Widget_Tabs
     */
    public function addTabAfter($tabId, $tab, $afterTabId)
    {
        $this->addTab($tabId, $tab);
        $this->_tabs[$tabId]->setAfter($afterTabId);
    }

    /**
     * Add new tab
     *
     * @param   string $tabId
     * @param   array|Varien_Object|string $tab
     * @return  Mage_Backend_Block_Widget_Tabs
     */
    public function addTab($tabId, $tab)
    {
        if (is_array($tab)) {
            $this->_tabs[$tabId] = new Varien_Object($tab);
        }
        elseif ($tab instanceof Varien_Object) {
            $this->_tabs[$tabId] = $tab;
            if (!$this->_tabs[$tabId]->hasTabId()) {
                $this->_tabs[$tabId]->setTabId($tabId);
            }
        }
        elseif (is_string($tab)) {
            if (strpos($tab, '_Block_')) {
                $this->_tabs[$tabId] = $this->getLayout()->createBlock(
                    $tab,
                    $this->getNameInLayout() . '_tab_' . $tabId
                );
            }
            elseif ($this->getChildBlock($tab)) {
                $this->_tabs[$tabId] = $this->getChildBlock($tab);
            }
            else {
                $this->_tabs[$tabId] = null;
            }

            if (!($this->_tabs[$tabId] instanceof Mage_Backend_Block_Widget_Tab_Interface)) {
                throw new Exception(Mage::helper('Mage_Backend_Helper_Data')->__('Wrong tab configuration.'));
            }
        }
        else {
            throw new Exception(Mage::helper('Mage_Backend_Helper_Data')->__('Wrong tab configuration.'));
        }

        if (is_null($this->_tabs[$tabId]->getUrl())) {
            $this->_tabs[$tabId]->setUrl('#');
        }

        if (!$this->_tabs[$tabId]->getTitle()) {
            $this->_tabs[$tabId]->setTitle($this->_tabs[$tabId]->getLabel());
        }

        $this->_tabs[$tabId]->setId($tabId);
        $this->_tabs[$tabId]->setTabId($tabId);

        if (is_null($this->_activeTab)) $this->_activeTab = $tabId;
        if (true === $this->_tabs[$tabId]->getActive()) $this->setActiveTab($tabId);

        return $this;
    }

    public function getActiveTabId()
    {
        return $this->getTabId($this->_tabs[$this->_activeTab]);
    }

    /**
     * Set Active Tab
     * Tab has to be not hidden and can show
     *
     * @param string $tabId
     * @return Mage_Backend_Block_Widget_Tabs
     */
    public function setActiveTab($tabId)
    {
        if (isset($this->_tabs[$tabId]) && $this->canShowTab($this->_tabs[$tabId])
            && !$this->getTabIsHidden($this->_tabs[$tabId])) {
            $this->_activeTab = $tabId;
            if (!(is_null($this->_activeTab)) && ($tabId !== $this->_activeTab)) {
                foreach ($this->_tabs as $id => $tab) {
                    $tab->setActive($id === $tabId);
                }
            }
        }
        return $this;
    }

    /**
     * Set Active Tab
     *
     * @param string $tabId
     * @return Mage_Backend_Block_Widget_Tabs
     */
    protected function _setActiveTab($tabId)
    {
        foreach ($this->_tabs as $id => $tab) {
            if ($this->getTabId($tab) == $tabId) {
                $this->_activeTab = $id;
                $tab->setActive(true);
                return $this;
            }
        }
        return $this;
    }

    protected function _beforeToHtml()
    {
        if ($activeTab = $this->getRequest()->getParam('active_tab')) {
            $this->setActiveTab($activeTab);
        } elseif ($activeTabId = Mage::getSingleton('Mage_Backend_Model_Auth_Session')->getActiveTabId()) {
            $this->_setActiveTab($activeTabId);
        }

        $_new = array();
        foreach( $this->_tabs  as $key => $tab ) {
            foreach( $this->_tabs  as $k => $t ) {
                if( $t->getAfter() == $key ) {
                    $_new[$key] = $tab;
                    $_new[$k] = $t;
                } else {
                    if( !$tab->getAfter() || !in_array($tab->getAfter(), array_keys($this->_tabs)) ) {
                        $_new[$key] = $tab;
                    }
                }
            }
        }

        $this->_tabs = $_new;
        unset($_new);

        $this->assign('tabs', $this->_tabs);
        return parent::_beforeToHtml();
    }

    public function getJsObjectName()
    {
        return $this->getId() . 'JsTabs';
    }

    public function getTabsIds()
    {
        if (empty($this->_tabs))
            return array();
        return array_keys($this->_tabs);
    }

    public function getTabId($tab, $withPrefix = true)
    {
        if ($tab instanceof Mage_Backend_Block_Widget_Tab_Interface) {
            return ($withPrefix ? $this->getId().'_' : '').$tab->getTabId();
        }
        return ($withPrefix ? $this->getId().'_' : '').$tab->getId();
    }

    public function canShowTab($tab)
    {
        if ($tab instanceof Mage_Backend_Block_Widget_Tab_Interface) {
            return $tab->canShowTab();
        }
        return true;
    }

    public function getTabIsHidden($tab)
    {
        if ($tab instanceof Mage_Backend_Block_Widget_Tab_Interface) {
            return $tab->isHidden();
        }
        return $tab->getIsHidden();
    }

    public function getTabUrl($tab)
    {
        if ($tab instanceof Mage_Backend_Block_Widget_Tab_Interface) {
            if (method_exists($tab, 'getTabUrl')) {
                return $tab->getTabUrl();
            }
            return '#';
        }
        if (!is_null($tab->getUrl())) {
            return $tab->getUrl();
        }
        return '#';
    }

    public function getTabTitle($tab)
    {
        if ($tab instanceof Mage_Backend_Block_Widget_Tab_Interface) {
            return $tab->getTabTitle();
        }
        return $tab->getTitle();
    }

    public function getTabClass($tab)
    {
        if ($tab instanceof Mage_Backend_Block_Widget_Tab_Interface) {
            if (method_exists($tab, 'getTabClass')) {
                return $tab->getTabClass();
            }
            return '';
        }
        return $tab->getClass();
    }


    public function getTabLabel($tab)
    {
        if ($tab instanceof Mage_Backend_Block_Widget_Tab_Interface) {
            return $tab->getTabLabel();
        }
        return $tab->getLabel();
    }

    public function getTabContent($tab)
    {
        if ($tab instanceof Mage_Backend_Block_Widget_Tab_Interface) {
            if ($tab->getSkipGenerateContent()) {
                return '';
            }
            return $tab->toHtml();
        }
        return $tab->getContent();
    }

    /**
     * Mark tabs as dependant of each other
     * Arbitrary number of tabs can be specified, but at least two
     *
     * @param string $tabOneId
     * @param string $tabTwoId
     * @param string $tabNId...
     */
    public function bindShadowTabs($tabOneId, $tabTwoId)
    {
        $tabs = array();
        $args = func_get_args();
        if ((!empty($args)) && (count($args) > 1)) {
            foreach ($args as $tabId) {
                if (isset($this->_tabs[$tabId])) {
                    $tabs[$tabId] = $tabId;
                }
            }
            $blockId = $this->getId();
            foreach ($tabs as $tabId) {
                foreach ($tabs as $tabToId) {
                    if ($tabId !== $tabToId) {
                        if (!$this->_tabs[$tabToId]->getData('shadow_tabs')) {
                            $this->_tabs[$tabToId]->setData('shadow_tabs', array());
                        }
                        $this->_tabs[$tabToId]->setData('shadow_tabs', array_merge(
                            $this->_tabs[$tabToId]->getData('shadow_tabs'),
                            array($blockId . '_' . $tabId)
                        ));
                    }
                }
            }
        }
    }

    /**
     * Obtain shadow tabs information
     *
     * @param bool $asJson
     * @return array|string
     */
    public function getAllShadowTabs($asJson = true)
    {
        $result = array();
        if (!empty($this->_tabs)) {
            $blockId = $this->getId();
            foreach (array_keys($this->_tabs) as $tabId) {
                if ($this->_tabs[$tabId]->getData('shadow_tabs')) {
                    $result[$blockId . '_' . $tabId] = $this->_tabs[$tabId]->getData('shadow_tabs');
                }
            }
        }
        if ($asJson) {
            return Mage::helper('Mage_Core_Helper_Data')->jsonEncode($result);
        }
        return $result;
    }

    /**
     * Set tab property by tab's identifier
     *
     * @param string $tab
     * @param string $key
     * @param mixed $value
     * @return Mage_Backend_Block_Widget_Tabs
     */
    public function setTabData($tab, $key, $value)
    {
        if (isset($this->_tabs[$tab]) && $this->_tabs[$tab] instanceof Varien_Object) {
            if ($key == 'url') {
                $value = $this->getUrl($value, array('_current' => true, '_use_rewrite' => true));
            }
            $this->_tabs[$tab]->setData($key, $value);
        }

        return $this;
    }

    /**
     * Removes tab with passed id from tabs block
     *
     * @param string $tabId
     * @return Mage_Backend_Block_Widget_Tabs
     */
    public function removeTab($tabId)
    {
        if (isset($this->_tabs[$tabId])) {
            unset($this->_tabs[$tabId]);
        }
        return $this;
    }
}

