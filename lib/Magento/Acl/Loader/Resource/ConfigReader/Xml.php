<?php
/**
 * Magento Acl Resources reader
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
class Magento_Acl_Loader_Resource_ConfigReader_Xml extends Magento_Config_XmlAbstract
    implements Magento_Acl_Loader_Resource_ConfigReaderInterface
{
    /**
     * Xml to array resource tree converter
     *
     * @var Magento_Config_Dom_Converter_ArrayConverter
     */
    protected $_converter;

    /**
     * Configuration array format mapper
     *
     * @var Magento_Acl_Loader_Resource_ConfigReader_Xml_ArrayMapper
     */
    protected $_mapper;

    /**
     * @param Magento_Acl_Loader_Resource_ConfigReader_FileListInterface $fileList
     * @param Magento_Acl_Loader_Resource_ConfigReader_Xml_ArrayMapper $mapper
     * @param Magento_Config_Dom_Converter_ArrayConverter $converter
     */
    public function __construct(
        Magento_Acl_Loader_Resource_ConfigReader_FileListInterface $fileList,
        Magento_Acl_Loader_Resource_ConfigReader_Xml_ArrayMapper $mapper,
        Magento_Config_Dom_Converter_ArrayConverter $converter
    ) {
        parent::__construct($fileList->asArray());
        $this->_converter = $converter;
        $this->_mapper = $mapper;
    }

    /**
     * Get absolute path to the XML-schema file
     *
     * @return string
     */
    public function getSchemaFile()
    {
        return realpath(__DIR__ . '/../../../etc/acl.xsd');
    }

    /**
     * Extract configuration data from the DOM structure
     *
     * @param DOMDocument $dom
     * @return array
     */
    protected function _extractData(DOMDocument $dom)
    {
        return array();
    }

    /**
     * Get XML-contents, initial for merging
     *
     * @return string
     */
    protected function _getInitialXml()
    {
        return '<?xml version="1.0" encoding="utf-8"?><config><acl></acl></config>';
    }

    /**
     * Get list of paths to identifiable nodes
     *
     * @return array
     */
    protected function _getIdAttributes()
    {
        return array();
    }

    /**
     * Get Dom configuration model
     *
     * @return Magento_Config_Dom
     */
    protected function _getDomConfigModel()
    {
        if (is_null($this->_domConfig)) {
            $this->_domConfig = new Magento_Acl_Loader_Resource_ConfigReader_Xml_Dom(
                $this->_getInitialXml(),
                $this->_getIdAttributes()
            );
        }
        return $this->_domConfig;
    }

    /**
     * Get if xml files must be runtime validated
     *
     * @return boolean
     */
    protected function _isRuntimeValidated()
    {
        return false;
    }

    /**
     * Retrieve ACL resources
     *
     * @return array
     */
    public function getAclResources()
    {
        $xpath = new DOMXPath($this->_getDomConfigModel()->getDom());
        $xmlAsArray = $this->_converter->convert($xpath->query('/config/acl/resources/*'));
        if (isset($xmlAsArray['resource'])) {
            return $this->_mapper->map($xmlAsArray['resource']);
        } else {
            return array();
        }
    }
}
