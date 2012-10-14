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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Menu configuration files handler
 */
class Mage_Backend_Model_Menu_Config_Menu extends Magento_Config_XmlAbstract
{
    /**
     * Path to menu.xsd
     *
     * @return string
     */
    public function getSchemaFile()
    {
        return __DIR__ . '/menu.xsd';
    }

    /**
     * Extract configuration data from the DOM structure
     *
     * @param DOMDocument $domDocument
     * @return array
     */
    protected function _extractData(DOMDocument $domDocument)
    {
        return array();
    }

    /**
     * Getter for initial menu.xml contents
     *
     * @return string
     */
    protected function _getInitialXml()
    {
        return '<?xml version="1.0" encoding="utf-8"?><config><menu></menu></config>';
    }

    /**
     * Variables are identified by module and name
     *
     * @return array
     */
    protected function _getIdAttributes()
    {
        return array();
    }

    /**
     * Get merged configuration
     * @return DOMDocument
     */
    public function getMergedConfig()
    {
        return $this->_getDomConfigModel()->getDom();
    }

    /**
     * Get Dom configuration model
     * @return Mage_Backend_Model_Menu_Config_Menu_Dom
     */
    protected function _getDomConfigModel()
    {
        if (is_null($this->_domConfig)) {
            $this->_domConfig = new Mage_Backend_Model_Menu_Config_Menu_Dom(
                $this->_getInitialXml(),
                $this->_getIdAttributes()
            );
        }
        return $this->_domConfig;
    }

    /**
     * Perform xml validation
     * @return Magento_Config_XmlAbstract
     * @throws Magento_Exception if invalid XML-file passed
     */
    public function validate()
    {
        return $this->_performValidate();
    }

    /**
     * Get if xml files must be runtime validated
     * @return boolean
     */
    protected function _isRuntimeValidated()
    {
        return false;
    }
}
