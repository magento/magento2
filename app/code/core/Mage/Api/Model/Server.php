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
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Webservice api abstract
 *
 * @category   Mage
 * @package    Mage_Api
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Api_Model_Server
{

    /**
     * Api Name by Adapter
     * @var string
     */
    protected $_api = "";

    /**
     * Web service adapter
     *
     * @var Mage_Api_Model_Server_Adaper_Interface
     */
    protected $_adapter;

    public function init(Mage_Api_Controller_Action $controller, $adapter='default', $handler='default')
    {
        $adapters = Mage::getSingleton('Mage_Api_Model_Config')->getActiveAdapters();
        $handlers = Mage::getSingleton('Mage_Api_Model_Config')->getHandlers();
        $this->_api = $adapter;
        if (isset($adapters[$adapter])) {
            $adapterModel = Mage::getModel((string) $adapters[$adapter]->model);
            /* @var $adapterModel Mage_Api_Model_Server_Adapter_Interface */
            if (!($adapterModel instanceof Mage_Api_Model_Server_Adapter_Interface)) {
                Mage::throwException(Mage::helper('Mage_Api_Helper_Data')->__('Invalid webservice adapter specified.'));
            }

            $this->_adapter = $adapterModel;
            $this->_adapter->setController($controller);

            if (!isset($handlers->$handler)) {
                Mage::throwException(Mage::helper('Mage_Api_Helper_Data')->__('Invalid webservice handler specified.'));
            }

            $handlerClassName = Mage::getConfig()->getModelClassName((string) $handlers->$handler->model);
            $this->_adapter->setHandler($handlerClassName);
        } else {
            Mage::throwException(Mage::helper('Mage_Api_Helper_Data')->__('Invalid webservice adapter specified.'));
        }

        return $this;
    }

    /**
     * Run server
     *
     */
    public function run()
    {
        $this->getAdapter()->run();
    }

    /**
     * Get Api name by Adapter
     * @return string
     */
    public function getApiName()
    {
        return $this->_api;
    }

    /**
     * Retrieve web service adapter
     *
     * @return Mage_Api_Model_Server_Adaper_Interface
     */
    public function getAdapter()
    {
        return $this->_adapter;
    }


} // Class Mage_Api_Model_Server_Abstract End
