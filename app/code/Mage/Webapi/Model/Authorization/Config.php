<?php
/**
 * API ACL Config model
 *
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Webapi_Model_Authorization_Config implements Mage_Core_Model_Acl_Config_ConfigInterface
{

    const ACL_RESOURCES_XPATH = '/config/acl/resources/*';

    const ACL_VIRTUAL_RESOURCES_XPATH = '/config/mapping/*';

    /**
     * Module configuration reader
     *
     * @var Mage_Core_Model_Config_Modules_Reader
     */
    protected $_moduleReader;

    /**
     * @var Magento_Acl_Config_Reader
     */
    protected $_reader;

    /**
     * @var Mage_Webapi_Model_Authorization_Config_Reader_Factory
     */
    protected $_readerFactory;

    /**
     * @param Mage_Core_Model_Config_Modules_Reader $moduleReader
     * @param Mage_Webapi_Model_Authorization_Config_Reader_Factory $readerFactory
     */
    public function __construct(
        Mage_Core_Model_Config_Modules_Reader $moduleReader,
        Mage_Webapi_Model_Authorization_Config_Reader_Factory $readerFactory
    ) {
        $this->_moduleReader = $moduleReader;
        $this->_readerFactory = $readerFactory;
    }

    /**
     * Retrieve list of ACL files from each module.
     *
     * @return array
     */
    protected function _getAclResourceFiles()
    {
        $files = $this->_moduleReader->getModuleConfigurationFiles('webapi' . DIRECTORY_SEPARATOR . 'acl.xml');
        return (array)$files;
    }

    /**
     * Reader object initialization.
     *
     * @return Magento_Acl_Config_Reader
     */
    protected function _getReader()
    {
        if (is_null($this->_reader)) {
            $aclResourceFiles = $this->_getAclResourceFiles();
            $this->_reader = $this->_readerFactory->createReader(array('configFiles'  => $aclResourceFiles));
        }
        return $this->_reader;
    }

    /**
     * Get DOMXPath with loaded resources inside.
     *
     * @return DOMXPath
     */
    protected function _getXPathResources()
    {
        $aclResources = $this->_getReader()->getAclResources();
        return new DOMXPath($aclResources);
    }

    /**
     * Return ACL Resources.
     *
     * @return DOMNodeList
     */
    public function getAclResources()
    {
        return $this->_getXPathResources()->query(self::ACL_RESOURCES_XPATH);
    }

    /**
     * Return array representation of ACL resources.
     *
     * @param bool $includeRoot If FALSE then only children of root element will be returned
     * @return array
     */
    public function getAclResourcesAsArray($includeRoot = true)
    {
        $result = array();
        $rootResource = null;
        $resources = $this->getAclResources();

        if ($resources && $resources->length == 1) {
            $rootResource = $resources->item(0);
        }

        if ($rootResource && $rootResource->childNodes
            && (string)$rootResource->getAttribute('id') == Mage_Webapi_Model_Authorization::API_ACL_RESOURCES_ROOT_ID
        ) {
            $result = $this->_parseAclResourceDOMElement($rootResource);
        }

        if (!$includeRoot) {
            $result = isset($result['children']) ? $result['children'] : array();
        }
        return $result;
    }

    /**
     * Parse DOMElement of ACL resource in config and return its array representation.
     *
     * @param DOMElement $node
     * @return array
     */
    protected function _parseAclResourceDOMElement(DOMElement $node)
    {
        $result = array();

        $result['id'] = (string)$node->getAttribute('id');
        $result['text'] = (string)$node->getAttribute('title');
        $sortOrder = (string)$node->getAttribute('sortOrder');
        if (!empty($sortOrder)) {
            $result['sortOrder']= $sortOrder;
        }

        $result['children'] = array();
        foreach ($node->childNodes as $child) {
            if ($child instanceof DOMElement) {
                $result['children'][] = $this->_parseAclResourceDOMElement($child);
            }
        }

        if (!empty($result['children'])) {
            $result['children'] = $this->_getSortedBySortOrder($result['children']);
        }

        return $result;
    }

    /**
     * Get array elements sorted by sortOrder key
     *
     * @param array $elements
     * @return array
     */
    protected function _getSortedBySortOrder(array $elements)
    {
        $sortable = array();
        $unsortable = array();
        foreach ($elements as $element) {
            if (isset($element['sortOrder'])) {
                $sortable[] = $element;
            } else {
                $unsortable[] = $element;
            }
        }
        usort($sortable, function ($firstItem, $secondItem) {
            // To preserve the original order in the array, return 1 when $firstItem == $secondItem instead of 0
            return $firstItem['sortOrder'] < $secondItem['sortOrder'] ? -1 : 1;
        });
        // Move un-sortable elements to the end of array to preserve their original order between each other
        return array_merge($sortable, $unsortable);
    }

    /**
     * Return ACL Virtual Resources.
     *
     * Virtual resources are not shown in resource list, they use existing resource to check permission.
     *
     * @return DOMNodeList
     */
    public function getAclVirtualResources()
    {
        return $this->_getXPathResources()->query(self::ACL_VIRTUAL_RESOURCES_XPATH);
    }
}
