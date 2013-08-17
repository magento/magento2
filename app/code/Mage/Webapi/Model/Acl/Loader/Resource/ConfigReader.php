<?php
/**
 * API ACL Config Reader model.
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
class Mage_Webapi_Model_Acl_Loader_Resource_ConfigReader extends Magento_Acl_Loader_Resource_ConfigReader_Xml
{
    const ACL_VIRTUAL_RESOURCES_XPATH = '/config/mapping/*';

    /**
     * Application config
     *
     * @var Mage_Core_Model_Config
     */
    protected $_config;

    /**
     * @param Mage_Webapi_Model_Acl_Loader_Resource_ConfigReader_FileList $fileList
     * @param Magento_Acl_Loader_Resource_ConfigReader_Xml_ArrayMapper $mapper
     * @param Magento_Config_Dom_Converter_ArrayConverter $converter
     * @param Mage_Core_Model_Config $config
     */
    public function __construct(
        Mage_Webapi_Model_Acl_Loader_Resource_ConfigReader_FileList $fileList,
        Magento_Acl_Loader_Resource_ConfigReader_Xml_ArrayMapper $mapper,
        Magento_Config_Dom_Converter_ArrayConverter $converter,
        Mage_Core_Model_Config $config
    ) {
        if (count($fileList->asArray())) {
            parent::__construct($fileList, $mapper, $converter);
        } else {
            $this->_mapper = $mapper;
            $this->_converter = $converter;
        }

        $this->_config = $config;
    }

    /**
     * Get absolute path to the XML-schema file.
     *
     * @return string
     */
    public function getSchemaFile()
    {
        return $this->_config->getModuleDir('etc', 'Mage_Webapi') . DIRECTORY_SEPARATOR . 'acl.xsd';
    }

    /**
     * Get XML-contents, initial for merging.
     *
     * @return string
     */
    protected function _getInitialXml()
    {
        return '<?xml version="1.0" encoding="utf-8"?><config><acl></acl><mapping></mapping></config>';
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
        $xpath = new DOMXPath($this->_getDomConfigModel()->getDom());
        return $xpath->query(self::ACL_VIRTUAL_RESOURCES_XPATH);
    }
}
