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
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Admin configuration model
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Model_Config extends Varien_Simplexml_Config
{

    /**
     * Enter description here...
     *
     * @var Varien_Simplexml_Element
     */
    protected $_sections;

    /**
     * Tabs
     *
     * @var Varien_Simplexml_Element
     */
    protected $_tabs;

    /**
     * Main Application object
     *
     * @var Mage_Core_Model_App
     */
    protected $_app;

    /**
     * Initializes XML for this configuration
     *
     * @param array $arguments
     */
    public function __construct(array $arguments = array())
    {
        $this->_app = isset($arguments['app']) ? $arguments['app'] : Mage::app();
        $sourceData = isset($arguments['data']) ? $arguments['data'] : array();
        return parent::__construct($sourceData);
    }

    /**
     * Enter description here...
     *
     * @param string $sectionCode
     * @param string $websiteCode
     * @param string $storeCode
     * @return Varien_Simplexml_Element
     */
    public function getSections($sectionCode=null, $websiteCode=null, $storeCode=null)
    {
        if (empty($this->_sections)) {
            $this->_initSectionsAndTabs();
        }

        return $this->_sections;
    }

    /**
     * Retrive tabs
     *
     * @return Varien_Simplexml_Element
     */
    public function getTabs()
    {
        if (empty($this->_tabs)) {
            $this->_initSectionsAndTabs();
        }

        return $this->_tabs;
    }

    protected function _initSectionsAndTabs()
    {
        $mergeConfig = Mage::getModel('Mage_Core_Model_Config_Base');

        $config = Mage::getConfig()->loadModulesConfiguration('system.xml');

        $this->_sections = $config->getNode('sections');

        $this->_tabs = $config->getNode('tabs');
    }



    /**
     * Enter description here...
     *
     * @param string $sectionCode
     * @param string $websiteCode
     * @param string $storeCode
     * @return Varien_Simplexml_Element
     */
    public function getSection($sectionCode=null, $websiteCode=null, $storeCode=null)
    {

        if ($sectionCode){
            return  $this->getSections()->$sectionCode;
        } elseif ($websiteCode) {
            return  $this->getSections()->$websiteCode;
        } elseif ($storeCode) {
            return  $this->getSections()->$storeCode;
        }
    }

    /**
     * Check whether node has child node that can be shown
     *
     * @param Varien_Simplexml_Element $node
     * @param string $websiteCode
     * @param string $storeCode
     * @return boolean
     */
    public function hasChildren($node, $websiteCode = null, $storeCode = null)
    {
        if (!$this->_canShowNode($node, $websiteCode, $storeCode)) {
            return false;
        }

        if (isset($node->groups)) {
            $children = $node->groups->children();
        } elseif (isset($node->fields)) {
            $children = $node->fields->children();
        } else {
            return true;
        }

        foreach ($children as $child) {
            if ($this->hasChildren($child, $websiteCode, $storeCode)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Checks whether it is possible to show the node
     *
     * @param Varien_Simplexml_Element $node
     * @param string $websiteCode
     * @param string $storeCode
     * @return boolean
     */
    protected function _canShowNode($node, $websiteCode = null, $storeCode = null)
    {
        $showTab = false;
        if ($storeCode) {
            $showTab = (int)$node->show_in_store;
        } elseif ($websiteCode) {
            $showTab = (int)$node->show_in_website;
        } elseif (!empty($node->show_in_default)) {
            $showTab = true;
        }

        $showTab = $showTab || $this->_app->isSingleStoreMode();
        $showTab = $showTab && !($this->_app->isSingleStoreMode() && (int)$node->hide_in_single_store_mode);
        return $showTab;
    }

    /**
     * Get translate module name
     *
     * @param Varien_Simplexml_Element $sectionNode
     * @param Varien_Simplexml_Element $groupNode
     * @param Varien_Simplexml_Element $fieldNode
     * @return string
     */
    function getAttributeModule($sectionNode = null, $groupNode = null, $fieldNode = null)
    {
        $moduleName = 'Mage_Adminhtml';
        if (is_object($sectionNode) && method_exists($sectionNode, 'attributes')) {
            $sectionAttributes = $sectionNode->attributes();
            $moduleName = isset($sectionAttributes['module']) ? (string)$sectionAttributes['module'] : $moduleName;
        }
        if (is_object($groupNode) && method_exists($groupNode, 'attributes')) {
            $groupAttributes = $groupNode->attributes();
            $moduleName = isset($groupAttributes['module']) ? (string)$groupAttributes['module'] : $moduleName;
        }
        if (is_object($fieldNode) && method_exists($fieldNode, 'attributes')) {
            $fieldAttributes = $fieldNode->attributes();
            $moduleName = isset($fieldAttributes['module']) ? (string)$fieldAttributes['module'] : $moduleName;
        }

        return $moduleName;
    }

    /**
     * System configuration section, fieldset or field label getter
     *
     * @param string $sectionName
     * @param string $groupName
     * @param string $fieldName
     * @return string
     */
    public function getSystemConfigNodeLabel($sectionName, $groupName = null, $fieldName = null)
    {
        $sectionName = trim($sectionName, '/');
        $path = '//sections/' . $sectionName;
        $groupNode = $fieldNode = null;
        $sectionNode = $this->_sections->xpath($path);
        if (!empty($groupName)) {
            $path .= '/groups/' . trim($groupName, '/');
            $groupNode = $this->_sections->xpath($path);
        }
        if (!empty($fieldName)) {
            if (!empty($groupName)) {
                $path .= '/fields/' . trim($fieldName, '/');
                $fieldNode = $this->_sections->xpath($path);
            }
            else {
                Mage::throwException(Mage::helper('Mage_Adminhtml_Helper_Data')->__('The group node name must be specified with field node name.'));
            }
        }
        $moduleName = $this->getAttributeModule($sectionNode, $groupNode, $fieldNode);
        $systemNode = $this->_sections->xpath($path);
        foreach ($systemNode as $node) {
            return Mage::helper($moduleName)->__((string)$node->label);
        }
        return '';
    }

    /**
     * Look for encrypted node entries in all system.xml files and return them
     *
     * @return array $paths
     */
    public function getEncryptedNodeEntriesPaths($explodePathToEntities = false)
    {
        $paths = array();
        $configSections = $this->getSections();
        if ($configSections) {
            foreach ($configSections->xpath('//sections/*/groups/*/fields/*/backend_model') as $node) {
                if ('adminhtml/system_config_backend_encrypted' === (string)$node) {
                    $section = $node->getParent()->getParent()->getParent()->getParent()->getParent()->getName();
                    $group   = $node->getParent()->getParent()->getParent()->getName();
                    $field   = $node->getParent()->getName();
                    if ($explodePathToEntities) {
                        $paths[] = array('section' => $section, 'group' => $group, 'field' => $field);
                    }
                    else {
                        $paths[] = $section . '/' . $group . '/' . $field;
                    }
                }
            }
        }
        return $paths;
    }
}
