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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Store switcher block
 *
 * @category   Mage
 * @package    Mage_Backend
 * @author     Magento Core Team <core@magentocommerce.com>
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Mage_Backend_Block_Store_Switcher extends Mage_Backend_Block_Template
{
    /**
     * Key in config for store switcher hint
     */
    const XPATH_HINT_KEY = 'store_switcher';

    /**
     * @var array
     */
    protected $_storeIds;

    /**
     * Name of store variable
     *
     * @var string
     */
    protected $_storeVarName = 'store';

    /**
     * Url for store switcher hint
     *
     * @var string
     */
    protected $_hintUrl;

    /**
     * @var bool
     */
    protected $_hasDefaultOption = true;

    /**
     * Block template filename
     *
     * @var string
     */
    protected $_template = 'Mage_Backend::store/switcher.phtml';

    /**
     * Application model
     *
     * @var Mage_Core_Model_App
     */
    protected $_application;

    /**
     * Website factory
     *
     * @var Mage_Core_Model_Website_Factory
     */
    protected $_websiteFactory;

    /**
     * Store Group Factory
     *
     * @var Mage_Core_Model_Store_Group_Factory
     */
    protected $_storeGroupFactory;

    /**
     * Constructor
     *
     * @param Mage_Core_Block_Template_Context $context
     * @param Mage_Core_Model_App $application
     * @param Mage_Core_Model_Website_Factory $websiteFactory
     * @param Mage_Core_Model_Store_Group_Factory $storeGroupFactory
     * @param array $data
     */
    public function __construct(
        Mage_Core_Block_Template_Context $context,
        Mage_Core_Model_App $application,
        Mage_Core_Model_Website_Factory $websiteFactory,
        Mage_Core_Model_Store_Group_Factory $storeGroupFactory,
        array $data = array()
    ) {
        parent::__construct($context, $data);
        $this->_application = $application;
        $this->_websiteFactory = $websiteFactory;
        $this->_storeGroupFactory = $storeGroupFactory;
    }


    protected function _construct()
    {
        parent::_construct();

        $this->setUseConfirm(true);
        $this->setUseAjax(true);
        $this->setDefaultStoreName($this->__('All Store Views'));
    }

    /**
     * @return Mage_Core_Model_Resource_Website_Collection
     */
    public function getWebsiteCollection()
    {
        $collection = $this->_websiteFactory->create()->getResourceCollection();

        $websiteIds = $this->getWebsiteIds();
        if (!is_null($websiteIds)) {
            $collection->addIdFilter($this->getWebsiteIds());
        }

        return $collection->load();
    }

    /**
     * Get websites
     *
     * @return array
     */
    public function getWebsites()
    {
        $websites = $this->_application->getWebsites();
        if ($websiteIds = $this->getWebsiteIds()) {
            foreach (array_keys($websites) as $websiteId) {
                if (!in_array($websiteId, $websiteIds)) {
                    unset($websites[$websiteId]);
                }
            }
        }
        return $websites;
    }

    /**
     * @param int|Mage_Core_Model_Website $website
     * @return Mage_Core_Model_Resource_Store_Group_Collection
     */
    public function getGroupCollection($website)
    {
        if (!$website instanceof Mage_Core_Model_Website) {
            $website = $this->_websiteFactory->create()->load($website);
        }
        return $website->getGroupCollection();
    }

    /**
     * Get store groups for specified website
     *
     * @param Mage_Core_Model_Website|int $website
     * @return array
     */
    public function getStoreGroups($website)
    {
        if (!$website instanceof Mage_Core_Model_Website) {
            $website = $this->_application->getWebsite($website);
        }
        return $website->getGroups();
    }

    /**
     * @param Mage_Core_Model_Store_Group|int $group
     * @return Mage_Core_Model_Resource_Store_Collection
     */
    public function getStoreCollection($group)
    {
        if (!$group instanceof Mage_Core_Model_Store_Group) {
            $group = $this->_storeGroupFactory->create()->load($group);
        }
        $stores = $group->getStoreCollection();
        $_storeIds = $this->getStoreIds();
        if (!empty($_storeIds)) {
            $stores->addIdFilter($_storeIds);
        }
        return $stores;
    }

    /**
     * Get store views for specified store group
     *
     * @param Mage_Core_Model_Store_Group|int $group
     * @return array
     */
    public function getStores($group)
    {
        if (!$group instanceof Mage_Core_Model_Store_Group) {
            $group = $this->_application->getGroup($group);
        }
        $stores = $group->getStores();
        if ($storeIds = $this->getStoreIds()) {
            foreach (array_keys($stores) as $storeId) {
                if (!in_array($storeId, $storeIds)) {
                    unset($stores[$storeId]);
                }
            }
        }
        return $stores;
    }

    /**
     * @return string
     */
    public function getSwitchUrl()
    {
        if ($url = $this->getData('switch_url')) {
            return $url;
        }
        return $this->getUrl('*/*/*', array('_current' => true, $this->_storeVarName => null));
    }

    /**
     * @param string $varName
     * @return Mage_Backend_Block_Store_Switcher
     */
    public function setStoreVarName($varName)
    {
        $this->_storeVarName = $varName;
        return $this;
    }

    /**
     * @return int
     */
    public function getStoreId()
    {
        return $this->getRequest()->getParam($this->_storeVarName);
    }

    /**
     * @param array $storeIds
     * @return Mage_Backend_Block_Store_Switcher
     */
    public function setStoreIds($storeIds)
    {
        $this->_storeIds = $storeIds;
        return $this;
    }

    /**
     * @return array
     */
    public function getStoreIds()
    {
        return $this->_storeIds;
    }

    /**
     * @return bool
     */
    public function isShow()
    {
        return !$this->_application->isSingleStoreMode();
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->_application->isSingleStoreMode()) {
            return parent::_toHtml();
        }
        return '';
    }

    /**
     * Set/Get whether the switcher should show default option
     *
     * @param bool $hasDefaultOption
     * @return bool
     */
    public function hasDefaultOption($hasDefaultOption = null)
    {
        if (null !== $hasDefaultOption) {
            $this->_hasDefaultOption = $hasDefaultOption;
        }
        return $this->_hasDefaultOption;
    }

    /**
     * Return url for store switcher hint
     *
     * @return string
     */
    public function getHintUrl()
    {
        if (null === $this->_hintUrl) {
            $this->_hintUrl = $this->helper('Mage_Core_Helper_Hint')->getHintByCode(self::XPATH_HINT_KEY);
        }
        return $this->_hintUrl;
    }

    /**
     * Return store switcher hint html
     *
     * @return string
     */
    public function getHintHtml()
    {
        $html = '';
        $url = $this->getHintUrl();
        if ($url) {
            $html = '<a'
                . ' href="'. $this->escapeUrl($url) . '"'
                . ' onclick="this.target=\'_blank\'"'
                . ' title="' . $this->__('What is this?') . '"'
                . ' class="link-store-scope">'
                . $this->__('What is this?')
                . '</a>';
        }
        return $html;
    }
}
