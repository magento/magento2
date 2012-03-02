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
 * @category    Magento
 * @package     Framework
 * @subpackage  Config
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * View configuration files handler
 */
class Magento_Config_View extends Magento_Config_XmlAbstract
{
    /**
     * Path to view.xsd
     *
     * @return string
     */
    public function getSchemaFile()
    {
        return __DIR__ . '/view.xsd';
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
        $result = array();
        $xPath = new DOMXPath($this->_dom);
        /** @var DOMElement $item */
        foreach ($xPath->query("/view/vars[@module='{$module}']/var") as $item) {
            $result[$item->getAttribute('name')] = (string)$item->nodeValue;
        }
        return $result;
    }

    /**
     * Get value of a configuration option variable
     *
     * @param string $module
     * @param string $name
     * @return bool|string
     */
    public function getVarValue($module, $name)
    {
        $xPath = new DOMXPath($this->_dom);
        /** @var DOMElement $item */
        foreach ($xPath->query("/view/vars[@module='{$module}']/var[@name='{$name}']") as $item) {
            return (string)$item->nodeValue;
        }
        return false;
    }

    /**
     * Getter for initial view.xml contents
     *
     * @return string
     */
    protected function _getInitialXml()
    {
        return '<?xml version="1.0" encoding="UTF-8"?><view></view>';
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
