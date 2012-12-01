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
 * System configuration structure reader
 */
class Mage_Backend_Model_Config_Structure extends Magento_Config_XmlAbstract
    implements Mage_Backend_Model_Config_StructureInterface
{
    /**
     * Main Application object
     *
     * @var Mage_Core_Model_App
     */
    protected $_app;

    /**
     * Config structure toArray converter
     *
     * @var Mage_Backend_Model_Config_Structure_Converter
     */
    protected $_converter;

    /**
     * List of encrypted paths
     *
     * @var array
     */
    protected $_encryptedPaths = array();

    /**
     * @var Mage_Core_Model_Factory_Helper
     */
    protected $_helperFactory;

    /**
     * @param array $data
     */
    public function __construct(array $data = array())
    {
        $this->_app = isset($data['app']) ? $data['app'] : Mage::app();
        $this->_converter = isset($data['converter'])
            ? $data['converter']
            : Mage::getSingleton('Mage_Backend_Model_Config_Structure_Converter');
        $this->_helperFactory = isset($data['helperFactory'])
            ? $data['helperFactory']
            : Mage::getSingleton('Mage_Core_Model_Factory_Helper');
        parent::__construct($data['sourceFiles']);
    }

    public function __wakeUp()
    {
        $this->_app = Mage::app();
        $this->_helperFactory = Mage::getObjectManager()->get('Mage_Core_Model_Factory_Helper');
    }

    /**
     * Get absolute path to the XML-schema file
     *
     * @return string
     */
    public function getSchemaFile()
    {
        return __DIR__ . '/Structure/system.xsd';
    }

    /**
     * Get absolute path to the XML-schema file
     *
     * @return string
     */
    public function getPerFileSchemaFile()
    {
        return __DIR__ . '/Structure/system_file.xsd';
    }

    /**
     * Extract configuration data from the DOM structure
     *
     * @param DOMDocument $dom
     * @return array|DOMNodeList
     */
    protected function _extractData(DOMDocument $dom)
    {
        $data = $this->_converter->convert($dom);
        return $data['config']['system'];
    }

    /**
     * Get XML-contents, initial for merging
     *
     * @return string
     */
    protected function _getInitialXml()
    {
        return '<?xml version="1.0" encoding="utf-8"?><config><system></system></config>';
    }

    /**
     * Get list of paths to identifiable nodes
     *
     * @return array
     */
    protected function _getIdAttributes()
    {
        return array(
            '/config/system/tab' => 'id',
            '/config/system/section' => 'id',
            '/config/system/section/group' => 'id',
            '/config/system/section/group/field' => 'id',
        );
    }

    /**
     * Retrieve all sections system configuration layout
     *
     * @return array
     */
    public function getSections()
    {
        return $this->_data['sections'];
    }

    /**
     * Retrieve list of tabs from
     *
     * @return array
     */
    public function getTabs()
    {
        return $this->_data['tabs'];
    }

    /**
     * Retrieve defined section
     *
     * @param string $sectionCode
     * @param string $websiteCode
     * @param string $storeCode
     * @return array
     */
    public function getSection($sectionCode=null, $websiteCode=null, $storeCode=null)
    {
        $key = $sectionCode ?: $websiteCode ?: $storeCode;
        return isset($this->_data['sections'][$key]) ? $this->_data['sections'][$key] : null;
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

        if (isset($node['groups'])) {
            $children = $node['groups'];
        } elseif (isset($node['fields'])) {
            $children = $node['fields'];
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
     * @param mixed $node
     * @param string $websiteCode
     * @param string $storeCode
     * @return boolean
     */
    protected function _canShowNode($node, $websiteCode = null, $storeCode = null)
    {
        $showTab = false;
        if ($storeCode) {
            $showTab = isset($node['showInStore']) ? (int)$node['showInStore'] : false;
        } elseif ($websiteCode) {
            $showTab = isset($node['showInWebsite']) ? (int)$node['showInWebsite'] : false;
        } elseif (isset($node['showInDefault']) && $node['showInDefault']) {
            $showTab = true;
        }

        $showTab = $showTab || $this->_app->isSingleStoreMode();
        $showTab = $showTab && !($this->_app->isSingleStoreMode()
            && isset($node['hide_in_single_store_mode']) && $node['hide_in_single_store_mode']);
        return $showTab;
    }

    /**
     * Get translate module name
     *
     * @param array $sectionNode
     * @param array $groupNode
     * @param array $fieldNode
     * @return string
     */
    public function getAttributeModule($sectionNode = null, $groupNode = null, $fieldNode = null)
    {
        $moduleName = 'Mage_Backend';
        if (isset($sectionNode['module'])) {
            $moduleName = (string) $sectionNode['module'];
        }
        if (isset($groupNode['module'])) {
            $moduleName = (string) $groupNode['module'];
        }
        if (isset($fieldNode['module'])) {
            $moduleName = (string) $fieldNode['module'];
        }
        return $moduleName;
    }

    /**
     * System configuration section, fieldset or field label getter
     *
     * @param string $sectionName
     * @param string $groupName
     * @param string $fieldName
     * @throws InvalidArgumentException
     * @return string
     */
    public function getSystemConfigNodeLabel($sectionName, $groupName = null, $fieldName = null)
    {
        $sectionName = trim($sectionName, '/');
        $groupNode = $fieldNode = null;
        $sectionNode = isset($this->_data['sections'][$sectionName]) ? $this->_data['sections'][$sectionName] : null;
        if (!$sectionNode) {
            throw new InvalidArgumentException(
                $this->_helperFactory->get('Mage_Backend_Helper_Data')->__('Wrong section specified.')
            );
        }
        $currentNode = $sectionNode;
        if (!empty($groupName)) {
            $groupName = trim($groupName, '/');
            $groupNode = isset($sectionNode['groups'][$groupName]) ? $sectionNode['groups'][$groupName] : null;
            if (!$groupNode) {
                throw new InvalidArgumentException(
                    $this->_helperFactory->get('Mage_Backend_Helper_Data')->__('Wrong group specified.')
                );
            }
            $currentNode = $groupNode;
        }
        if (!empty($fieldName)) {
            if (!empty($groupNode)) {
                $fieldName = trim($fieldName, '/');
                $fieldNode = isset($groupNode['fields'][$fieldName]) ? $groupNode['fields'][$fieldName] : null;
                if (!$fieldNode) {
                    throw new InvalidArgumentException(
                        $this->_helperFactory->get('Mage_Backend_Helper_Data')->__('Wrong field specified.')
                    );
                }
                $currentNode = $fieldNode;
            } else {
                Mage::throwException(
                    $this->_helperFactory->get('Mage_Backend_Helper_Data')->__('The group node name must be specified with field node name.')
                );
            }
        }
        $moduleName = $this->getAttributeModule($sectionNode, $groupNode, $fieldNode);
        return isset($currentNode['label'])
            ? $this->_helperFactory->get($moduleName)->__((string)$currentNode['label'])
            : '';
    }

    /**
     * Look for encrypted node entries in all system.xml files and return them
     *
     * @param bool $explodePathToEntities
     * @return array
     */
    public function getEncryptedNodeEntriesPaths($explodePathToEntities = false)
    {
        if (!$this->_encryptedPaths) {
            $this->_encryptedPaths = $this->getFieldsByAttribute(
                'backend_model', 'Mage_Backend_Model_Config_Backend_Encrypted', $explodePathToEntities
            );
        }
        return $this->_encryptedPaths;
    }

    public function getFieldsByAttribute($attributeName, $attributeValue, $explodePathToEntities = false)
    {
        $result = array();
        foreach ($this->_data['sections'] as $section) {
            if (!isset($section['groups'])) {
                continue;
            }
            foreach ($section['groups'] as $group) {
                if (!isset($group['fields'])) {
                    continue;
                }
                foreach ($group['fields'] as $field) {
                    if (isset($field[$attributeName])
                        && $field[$attributeName] == $attributeValue
                    ) {
                        if ($explodePathToEntities) {
                            $result[] = array(
                                'section' => $section['id'], 'group' => $group['id'], 'field' => $field['id']
                            );
                        } else {
                            $result[] = $section['id'] . '/' . $group['id'] . '/' . $field['id'];
                        }
                    }
                }
            }
        }
        return $result;
    }
}
