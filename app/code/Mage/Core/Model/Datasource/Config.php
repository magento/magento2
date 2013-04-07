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
 * @package     Mage_Catalog
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Core_Model_Datasource_Config implements Magento_Datasource_Config_Interface
{
    const CONFIG_AREA = 'global';

    const CONFIG_NODE = 'service-calls';

    /**
     * @var Varien_Simplexml_Element
     */
    protected $_simpleXml;

    protected $_objectManager;

    protected $_elementClass = 'Varien_Simplexml_Element';

    protected $_loaded = false;
    /**
     * @var Mage_Core_Model_Config
     */
    protected $_config;

    public function __construct(Mage_Core_Model_Config $config)
    {
        $this->_config = $config;
        $this->init();
    }

    /**
     * @param $alias
     * @return array
     * @throws Mage_Core_Exception
     */
    public function getClassByAlias($alias)
    {
        $node = $this->_simpleXml->xpath("//service-call[@name='" . $alias . "']");

        if (count($node) == 0) {
            throw Mage::exception('Mage_Core', 'Service call with name "' . $alias . '" doesn\'t exist');
        }

        /** @var $node Mage_Core_Model_Config_Element */
        $node = current($node);

        $methodArguments = array();
        foreach ($node[0] as $child) {
            if ($child->getName() == 'arg') {
                $methodArguments[$child->getAttribute('name')] = (string)$child;
            }
        }

        $result = array(
            'class' => $node->getAttribute('service'),
            'retrieveMethod' => $node->getAttribute('method'),
            'methodArguments' => $methodArguments,
        );

        if (!$result['class']) {
            throw Mage::exception('Mage_Core', 'Invalid Service call ' . $alias . ', service type must be defined in the "service" attribute');
        }

        return $result;
    }

    /**
     * @return SimpleXMLElement
     */
    public function init()
    {
        $updatesRootPath = self::CONFIG_AREA . '/' . self::CONFIG_NODE;
        $sourcesRoot = Mage::app()->getConfig()->getNode($updatesRootPath);

        /* Layout update files declared in configuration */
        $callsStr = '<calls />';
        if ($sourcesRoot) {
            $callsStr = $this->_getServiceCallConfig($sourcesRoot);
        }

        $this->_simpleXml = simplexml_load_string($callsStr, $this->_elementClass);
        return $this->_simpleXml;
    }

    /**
     * @param Mage_Core_Model_Config_Element $sourcesRoot
     * @return string
     */
    public function _getServiceCallConfig($sourcesRoot)
    {
        $sourceFiles = array();
        foreach ($sourcesRoot->children() as $sourceNode) {
            $sourceFiles[] = $this->_getServiceCallsFile($sourceNode);
        }

        $callsStr = '';
        foreach ($sourceFiles as $filename) {
            $fileStr = file_get_contents($filename);

            /** @var $fileXml Mage_Core_Model_Layout_Element */
            $fileXml = simplexml_load_string($fileStr, $this->_elementClass);
            $callsStr .= $fileXml->innerXml();
        }
        return '<calls>' . $callsStr . '</calls>';
    }

    /**
     * @param Mage_Core_Model_Config_Element $sourceNode
     * @return string
     * @throws Magento_Exception
     */
    protected function _getServiceCallsFile($sourceNode)
    {
        $file = (string)$sourceNode->file;
        if (!$file) {
            $sourceNodePath = $sourceNode->getName();
            throw new Magento_Exception(
                "Service calls instruction '{$sourceNodePath}' must specify file."
            );
        }

        $nameParts = array();
        if (strpos($file, '/') !== false) {
            $nameParts = explode('/', $file);
        } else {
            throw new Magento_Exception("Module is missing in Service calls configuration: '{$file}'");
        }
        $filename = $this->_config->getModuleDir('etc', $nameParts[0]) . '/' . $nameParts[1];
        if (!is_readable($filename)) {
            throw new Magento_Exception("Service calls configuration file '{$filename}' doesn't exist or isn't readable.");
        }
        return $filename;
    }
}
