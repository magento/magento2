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
 * @package     Mage_Api
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Wsdl config model
 *
 * @category   Mage
 * @package    Mage_Api
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Api_Model_Wsdl_Config extends Mage_Api_Model_Wsdl_Config_Base
{
    protected static $_namespacesPrefix = null;

    /**
     * @var Mage_Core_Model_Config_Modules_Reader
     */
    protected $_configReader;

    /**
     * @var Mage_Core_Model_Cache_Type_Config
     */
    protected $_configCacheType;

    /**
     * @param Mage_Core_Model_Config_Modules_Reader $configReader
     * @param Mage_Core_Model_Cache_Type_Config $configCacheType
     * @param Varien_Simplexml_Element|null $sourceData
     */
    public function __construct(
        Mage_Core_Model_Config_Modules_Reader $configReader,
        Mage_Core_Model_Cache_Type_Config $configCacheType,
        $sourceData = null
    ) {
        parent::__construct($sourceData);
        $this->_configReader = $configReader;
        $this->_configCacheType = $configCacheType;
    }

    /**
     * Return wsdl content
     *
     * @return string
     */
    public function getWsdlContent()
    {
        return $this->_xml->asXML();
    }

    /**
     * Return namespaces with their prefix
     *
     * @return array
     */
    public static function getNamespacesPrefix()
    {
        if (is_null(self::$_namespacesPrefix)) {
            self::$_namespacesPrefix = array();
            $config = Mage::getSingleton('Mage_Api_Model_Config')->getNode('v2/wsdl/prefix')->children();
            foreach ($config as $prefix => $namespace) {
                self::$_namespacesPrefix[$namespace->asArray()] = $prefix;
            }
        }
        return self::$_namespacesPrefix;
    }

    protected function _loadCache($id)
    {
        return $this->_configCacheType->load($id);
    }

    protected function _saveCache($data, $id, $tags = array(), $lifetime = false)
    {
        return $this->_configCacheType->save($data, $id, $tags, $lifetime);
    }

    protected function _removeCache($id)
    {
        return $this->_configCacheType->remove($id);
    }

    public function init()
    {
        $cachedXml = $this->_configCacheType->load($this->_cacheId);
        if ($cachedXml) {
            $this->loadString($cachedXml);
        } else {
            $mergeWsdl = new Mage_Api_Model_Wsdl_Config_Base();
            $mergeWsdl->setHandler($this->getHandler());

            /** @var Mage_Api_Helper_Data $helper */
            $helper = Mage::helper('Mage_Api_Helper_Data');
            if ($helper->isWsiCompliant()) {
                /**
                 * Exclude Mage_Api wsdl xml file because it used for previous version
                 * of API wsdl declaration
                 */
                $mergeWsdl->addLoadedFile($this->_configReader->getModuleDir('etc', "Mage_Api") . DS . 'wsi.xml');

                $baseWsdlFile = $this->_configReader->getModuleDir('etc', "Mage_Api") . DS . 'wsi.xml';
                $this->loadFile($baseWsdlFile);
                $this->_configReader->loadModulesConfiguration('wsi.xml', $this, $mergeWsdl);
            } else {
                $baseWsdlFile = $this->_configReader->getModuleDir('etc', "Mage_Api") . DS . 'wsdl.xml';
                $this->loadFile($baseWsdlFile);
                $this->_configReader->loadModulesConfiguration('wsdl.xml', $this, $mergeWsdl);
            }

            $this->_configCacheType->save($this->getXmlString(), $this->_cacheId);
        }
        return $this;
    }

    /**
     * Return Xml of node as string
     *
     * @return string
     */
    public function getXmlString()
    {
        return $this->getNode()->asXML();
    }
}
