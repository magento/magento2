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
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Wsdl base config
 *
 * @category   Mage
 * @package    Mage_Api
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Api_Model_Wsdl_Config_Base extends Varien_Simplexml_Config
{
    protected $_handler = '';

    /**
     * @var Varien_Object
     */
    protected $_wsdlVariables = null;

    protected $_loadedFiles = array();

    public function __construct($sourceData=null)
    {
        $this->_elementClass = 'Mage_Api_Model_Wsdl_Config_Element';

        // remove wsdl parameter from query
        $queryParams = Mage::app()->getRequest()->getQuery();
        unset($queryParams['wsdl']);

        // set up default WSDL template variables
        $this->_wsdlVariables = new Varien_Object(
            array(
                'name' => 'Magento',
                'url'  => htmlspecialchars(Mage::getUrl('*/*/*', array('_query' => $queryParams)))
            )
        );
        parent::__construct($sourceData);
    }

    /**
     * Set handler
     *
     * @param string $handler
     * @return Mage_Api_Model_Wsdl_Config_Base
     */
    public function setHandler($handler)
    {
        $this->_handler = $handler;
        return $this;
    }

    /**
     * Get handler
     *
     * @return string
     */
    public function getHandler()
    {
        return $this->_handler;
    }

    /**
     * Processing file data
     *
     * @param string $text
     * @return string
     */
    public function processFileData($text)
    {
        /** @var $template Mage_Core_Model_Email_Template_Filter */
        $template = Mage::getModel('Mage_Core_Model_Email_Template_Filter');

        $this->_wsdlVariables->setHandler($this->getHandler());

        $template->setVariables(array('wsdl'=>$this->_wsdlVariables));

        return $template->filter($text);
    }

    public function addLoadedFile($file)
    {
        if (!in_array($file, $this->_loadedFiles)) {
            $this->_loadedFiles[] = $file;
        }
        return $this;
    }

    public function loadFile($file)
    {
        if (in_array($file, $this->_loadedFiles)) {
            return false;
        }
        $res = parent::loadFile($file);
        if ($res) {
            $this->addLoadedFile($file);
        }
        return $this;
    }

    /**
     * Set variable to be used in WSDL template processing
     *
     * @param string $key Varible key
     * @param string $value Variable value
     * @return Mage_Api_Model_Wsdl_Config_Base
     */
    public function setWsdlVariable($key, $value)
    {
        $this->_wsdlVariables->setData($key, $value);

        return $this;
    }
}
