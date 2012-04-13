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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Webservice XmlRpc adapter
 *
 * @category   Mage
 * @package    Mage_Api
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Api_Model_Server_Adapter_Xmlrpc
    extends Varien_Object
    implements Mage_Api_Model_Server_Adapter_Interface
{
     /**
      * XmlRpc Server
      *
      * @var Zend_XmlRpc_Server
      */
     protected $_xmlRpc = null;

     /**
     * Set handler class name for webservice
     *
     * @param string $handler
     * @return Mage_Api_Model_Server_Adapter_Xmlrpc
     */
    public function setHandler($handler)
    {
        $this->setData('handler', $handler);
        return $this;
    }

    /**
     * Retrive handler class name for webservice
     *
     * @return string
     */
    public function getHandler()
    {
        return $this->getData('handler');
    }

     /**
     * Set webservice api controller
     *
     * @param Mage_Api_Controller_Action $controller
     * @return Mage_Api_Model_Server_Adapter_Xmlrpc
     */
    public function setController(Mage_Api_Controller_Action $controller)
    {
         $this->setData('controller', $controller);
         return $this;
    }

    /**
     * Retrive webservice api controller. If no controller have been set - emulate it by the use of Varien_Object
     *
     * @return Mage_Api_Controller_Action|Varien_Object
     */
    public function getController()
    {
        $controller = $this->getData('controller');

        if (null === $controller) {
            $controller = new Varien_Object(
                array('request' => Mage::app()->getRequest(), 'response' => Mage::app()->getResponse())
            );

            $this->setData('controller', $controller);
        }
        return $controller;
    }

    /**
     * Run webservice
     *
     * @return Mage_Api_Model_Server_Adapter_Xmlrpc
     */
    public function run()
    {
        $apiConfigCharset = Mage::getStoreConfig("api/config/charset");

        $this->_xmlRpc = new Zend_XmlRpc_Server();
        $this->_xmlRpc->setEncoding($apiConfigCharset)
            ->setClass($this->getHandler());
        $this->getController()->getResponse()
            ->clearHeaders()
            ->setHeader('Content-Type','text/xml; charset='.$apiConfigCharset)
            ->setBody($this->_xmlRpc->handle());
        return $this;
    }

    /**
     * Dispatch webservice fault
     *
     * @param int $code
     * @param string $message
     */
    public function fault($code, $message)
    {
        throw new Zend_XmlRpc_Server_Exception($message, $code);
    }
} // Class Mage_Api_Model_Server_Adapter_Xmlrpc End
