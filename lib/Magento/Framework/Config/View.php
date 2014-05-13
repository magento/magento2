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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * View configuration files handler
 */
namespace Magento\Framework\Config;

class View extends \Magento\Framework\Config\AbstractXml
{
    /**
     * Path to view.xsd
     *
     * @return string
     */
    public function getSchemaFile()
    {
        return __DIR__ . '/etc/view.xsd';
    }

    /**
     * Extract configuration data from the DOM structure
     *
     * @param \DOMDocument $dom
     * @return array
     */
    protected function _extractData(\DOMDocument $dom)
    {
        $result = array();
        /** @var $varsNode \DOMElement */
        foreach ($dom->childNodes->item(0)/*root*/->childNodes as $varsNode) {
            $moduleName = $varsNode->getAttribute('module');
            /** @var $varNode \DOMElement */
            foreach ($varsNode->getElementsByTagName('var') as $varNode) {
                $varName = $varNode->getAttribute('name');
                $varValue = $varNode->nodeValue;
                $result[$moduleName][$varName] = $varValue;
            }
        }
        return $result;
    }

    /**
     * Get a list of variables in scope of specified module
     *
     * Returns array(<var_name> => <var_value>)
     *
     * @param string $module
     * @return array
     */
    public function getVars($module)
    {
        return isset($this->_data[$module]) ? $this->_data[$module] : array();
    }

    /**
     * Get value of a configuration option variable
     *
     * @param string $module
     * @param string $var
     * @return string|false
     */
    public function getVarValue($module, $var)
    {
        return isset($this->_data[$module][$var]) ? $this->_data[$module][$var] : false;
    }

    /**
     * Return copy of DOM
     *
     * @return \Magento\Framework\Config\Dom
     */
    public function getDomConfigCopy()
    {
        return clone $this->_getDomConfigModel();
    }

    /**
     * Getter for initial view.xml contents
     *
     * @return string
     */
    protected function _getInitialXml()
    {
        return '<?xml version="1.0" encoding="UTF-8"?>'.
               '<view xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"></view>';
    }

    /**
     * Variables are identified by module and name
     *
     * @return array
     */
    protected function _getIdAttributes()
    {
        return array('/view/vars' => 'module', '/view/vars/var' => 'name');
    }
}
