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
 * System configuration tabs block
 *
 * @method setTitle(string $title)
 *
 * @category   Mage
 * @package    Mage_Backend
 * @author     Magento Core Team <core@magentocommerce.com>
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Mage_Backend_Block_System_Config_Tabs extends Mage_Backend_Block_Widget
{
    /**
     * Tabs
     *
     * @var Mage_Backend_Model_Config_Structure_Element_Iterator
     */
    protected $_tabs;

    /**
     * Block template filename
     *
     * @var string
     */
    protected $_template = 'system/config/tabs.phtml';

    /**
     * Currently selected section id
     *
     * @var string
     */
    protected $_currentSectionId;

    /**
     * Current website code
     *
     * @var string
     */
    protected $_websiteCode;

    /**
     * Current store code
     *
     * @var string
     */
    protected $_storeCode;

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
     * @param Magento_Filesystem $filesystem
     * @param Mage_Backend_Model_Config_Structure $configStructure
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
        Magento_Filesystem $filesystem,
        Mage_Backend_Model_Config_Structure $configStructure,
        array $data = array()
    ) {
        parent::__construct($request, $layout, $eventManager, $urlBuilder, $translator, $cache, $designPackage,
            $session, $storeConfig, $frontController, $helperFactory, $filesystem, $data);
        $this->_tabs = $configStructure->getTabs();

        $this->setId('system_config_tabs');
        $this->setTitle($this->helper('Mage_Backend_Helper_Data')->__('Configuration'));
        $this->_currentSectionId = $this->getRequest()->getParam('section');

        $this->helper('Mage_Backend_Helper_Data')->addPageHelpUrl($this->getRequest()->getParam('section') . '/');
    }

    /**
     * Get all tabs
     *
     * @return Mage_Backend_Model_Config_Structure_Element_Iterator
     */
    public function getTabs()
    {
        return $this->_tabs;
    }

    /**
     * Retrieve section url by section id
     *
     * @param Mage_Backend_Model_Config_Structure_Element_Section $section
     * @return string
     */
    public function getSectionUrl(Mage_Backend_Model_Config_Structure_Element_Section $section)
    {
        return $this->getUrl('*/*/*', array('_current' => true, 'section' => $section->getId()));
    }

    /**
     * Check whether section should be displayed as active
     *
     * @param Mage_Backend_Model_Config_Structure_Element_Section $section
     * @return bool
     */
    public function isSectionActive(Mage_Backend_Model_Config_Structure_Element_Section $section)
    {
        return $section->getId() == $this->_currentSectionId;
    }
}

