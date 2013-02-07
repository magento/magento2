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
 * @package     Mage_DesignEditor
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Theme selectors tabs container
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Mage_DesignEditor_Block_Adminhtml_Theme_Selector_StoreView extends Mage_Backend_Block_Template
{
    /**
     * Website collection
     *
     * @var Mage_Core_Model_Resource_Website_Collection
     */
    protected $_websiteCollection;

    /**
     * @var Mage_Core_Model_Theme_Service
     */
    protected $_serviceModel;

    /**
     * @param Mage_Core_Controller_Request_Http $request
     * @param Mage_Core_Model_Layout $layout
     * @param Mage_Core_Model_Event_Manager $eventManager
     * @param Mage_Backend_Model_Url $urlBuilder
     * @param Mage_Core_Model_Translate $translator
     * @param Mage_Core_Model_Cache $cache
     * @param Mage_Core_Model_Design_Package $designPackage
     * @param Mage_Core_Model_Session $session
     * @param Mage_Core_Model_Store_Config $storeConfig
     * @param Mage_Core_Controller_Varien_Front $frontController
     * @param Mage_Core_Model_Factory_Helper $helperFactory
     * @param Mage_Core_Model_Dir $dirs
     * @param Mage_Core_Model_Logger $logger
     * @param Magento_Filesystem $filesystem
     * @param Mage_Core_Model_Resource_Website_Collection $websiteCollection
     * @param Mage_Core_Model_Theme_Service $serviceModel
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Mage_Core_Controller_Request_Http $request,
        Mage_Core_Model_Layout $layout,
        Mage_Core_Model_Event_Manager $eventManager,
        Mage_Backend_Model_Url $urlBuilder,
        Mage_Core_Model_Translate $translator,
        Mage_Core_Model_Cache $cache,
        Mage_Core_Model_Design_Package $designPackage,
        Mage_Core_Model_Session $session,
        Mage_Core_Model_Store_Config $storeConfig,
        Mage_Core_Controller_Varien_Front $frontController,
        Mage_Core_Model_Factory_Helper $helperFactory,
        Mage_Core_Model_Dir $dirs,
        Mage_Core_Model_Logger $logger,
        Magento_Filesystem $filesystem,
        Mage_Core_Model_Resource_Website_Collection $websiteCollection,
        Mage_Core_Model_Theme_Service $serviceModel,
        array $data = array()
    ) {
        $this->_websiteCollection = $websiteCollection;
        $this->_serviceModel = $serviceModel;

        parent::__construct($request, $layout, $eventManager, $urlBuilder, $translator, $cache, $designPackage,
            $session, $storeConfig, $frontController, $helperFactory, $dirs, $logger, $filesystem, $data
        );
    }

    /**
     * Get website collection with stores and store-views joined
     *
     * @return Mage_Core_Model_Resource_Website_Collection
     */
    public function getCollection()
    {
        return $this->_websiteCollection->joinGroupAndStore();
    }

    /**
     * Get website, stores and store-views
     *
     * @return Mage_Core_Model_Resource_Website_Collection
     */
    public function getWebsiteStructure()
    {
        $structure = array();
        $website = null;
        $store = null;
        $storeView = null;
        /** @var $row Mage_Core_Model_Website */
        foreach ($this->getCollection() as $row) {
            $website = $row->getName();
            $store = $row->getGroupTitle();
            $storeView = $row->getStoreTitle();
            if (!isset($structure[$website])) {
                $structure[$website] = array();
            }
            if (!isset($structure[$website][$store])) {
                $structure[$website][$store] = array();
            }
            $structure[$website][$store][$storeView] = (int)$row->getStoreId();
        }

        return $structure;
    }

    /**
     * Get assign to multiple storeview button
     *
     * @return string
     */
    public function getAssignSaveButtonHtml()
    {
        /** @var $assignSaveButton Mage_Backend_Block_Widget_Button */
        $assignSaveButton = $this->getLayout()->createBlock('Mage_Backend_Block_Widget_Button');
        $assignSaveButton->setData(array(
            'label'     => $this->__('Assign'),
            'class'     => 'action-save',
            'data_attribute' => array(
                'mage-init' => array(
                    'button' => array(
                        'event' => 'assign-save',
                        'target' => 'body',
                        'eventData' => array()
                    ),
                ),
            )
        ));

        return $assignSaveButton->toHtml();
    }

    /**
     * Get an array of stores grouped by theme customization it uses.
     *
     * The structure is the following:
     *   array(
     *      theme_id => array(store_id)
     *   )
     *
     * @return array
     */
    protected function _getStoresByThemes()
    {
        $assignedThemeIds = array_map(
            function ($theme) {
                return $theme->getId();
            },
            $this->_serviceModel->getAssignedThemeCustomizations()
        );

        $storesByThemes = array();
        foreach ($this->_serviceModel->getStoresByThemes() as $themeId => $stores) {
            /* NOTE
                We filter out themes not included to $assignedThemeIds array so we only get actually "assigned"
                themes. So if theme is assigned to store or website and used by store-view only via config fall-back
                mechanism it will not get to the resulting $storesByThemes array.
            */
            if (!in_array($themeId, $assignedThemeIds)) {
                continue;
            }

            $storesByThemes[$themeId] = array();
            /** @var $store Mage_Core_Model_Store */
            foreach ($stores as $store) {
                $storesByThemes[$themeId][] = (int)$store->getId();
            }
        }

        return $storesByThemes;
    }

    /**
     * Get the flag if there are multiple store-views in Magento
     *
     * @return bool
     */
    protected function _isMultipleStoreViewMode()
    {
        $isMultipleMode = false;
        $tmpStore = null;
        foreach ($this->_serviceModel->getStoresByThemes() as $stores) {
            /** @var $store Mage_Core_Model_Store */
            foreach ($stores as $store) {
                if ($tmpStore === null) {
                    $tmpStore = $store->getId();
                } elseif ($tmpStore != $store->getId()) {
                    $isMultipleMode = true;
                    break(2);
                }
            }
        }

        return $isMultipleMode;
    }

    /**
     * Get options for JS widget vde.themeSelector
     *
     * @return string
     */
    public function getOptionsJson()
    {
        $options = array();
        $options['storesByThemes'] = $this->_getStoresByThemes();
        $options['assignSaveUrl'] = $this->getUrl('*/*/assignThemeToStore');
        $options['afterAssignSaveUrl'] = $this->getUrl('*/*/launch');
        $options['isMultipleStoreViewMode'] = $this->_isMultipleStoreViewMode();

        /** @var $helper Mage_Core_Helper_Data */
        $helper = $this->helper('Mage_Core_Helper_Data');

        return $helper->jsonEncode($options);
    }
}
