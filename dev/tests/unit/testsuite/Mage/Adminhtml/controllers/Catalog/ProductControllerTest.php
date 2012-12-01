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
 * @package     Mage_Adminhtml
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/**
 *
 * Test class for Mage_Adminhtml_Catalog_ProductController
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */
require_once __DIR__ . '/../../../../../../../../'
    . 'app/code/core/Mage/Adminhtml/controllers/Catalog/ProductController.php';
class Mage_Adminhtml_Catalog_ProductControllerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var PHPUnit_Framework_MockObject_MockObject|Mage_Adminhtml_Catalog_ProductController
     */
    protected $_controller;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject|Zend_Controller_Request_Abstract
     */
    protected $_request;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject|Mage_Backend_Model_Session
     */
    protected $_sessionMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject|Magento_ObjectManager
     */
    protected $_objectManager;

    /**
     * @var Zend_Controller_Response_Abstract|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_response;

    protected function setUp()
    {
        $this->_request = $this->getMockBuilder('Mage_Core_Controller_Request_Http')
            ->setMethods(array('getPost', 'getParam'))->getMock();
        $this->_response = $this->getMockBuilder('Mage_Core_Controller_Response_Http')->getMock();
        $this->_objectManager = $this->getMockBuilder('Magento_ObjectManager')->getMock();
        $frontController = $this->getMockBuilder('Mage_Core_Controller_Varien_Front')->getMock();

        $helperMock = $this->getMockBuilder('Mage_Backend_Helper_Data')->disableOriginalConstructor()->getMock();
        $this->_sessionMock = $this->getMockBuilder('Mage_Backend_Model_Session')->disableOriginalConstructor()
            ->setMethods(array('addError', 'setProductData'))->getMock();
        $translatorMock = $this->getMockBuilder('Mage_Core_Model_Translate')->getMock();

        $this->_controller = $this->getMockBuilder('Mage_Adminhtml_Catalog_ProductController')
            ->setMethods(array('loadLayout', '_initProduct', '_initProductSave', '_redirect', '__'))
            ->setConstructorArgs(array(
                $this->_request,
                $this->_response,
                $this->_objectManager,
                $frontController,
                array(
                    'helper' => $helperMock,
                    'session' => $this->_sessionMock,
                    'translator' => $translatorMock,
                )
            ))
            ->getMock();
        $this->_controller->expects($this->any())->method('__')->will($this->returnArgument(0));
    }

    /**
     * Test for Mage_Adminhtml_Catalog_ProductController::saveAction
     */
    public function testSaveActionWithDangerRequest()
    {
        $data = array(
            'product' => array(
                'entity_id' => 234
            )
        );

        $productMock = $this->getMockBuilder('Mage_Catalog_Model_Product')
            ->setMethods(array('getIdFieldName', 'save', 'getSku'))
            ->disableOriginalConstructor()
            ->getMock();
        $productMock->expects($this->any())->method('getIdFieldName')->will($this->returnValue('entity_id'));
        $productMock->expects($this->never())->method('save');

        $this->_sessionMock->expects($this->once())->method('addError')->with('Unable to save product')
            ->will($this->returnValue($this->_sessionMock));
        $this->_sessionMock->expects($this->once())->method('setProductData');

        $this->_request->expects($this->any())->method('getPost')->will($this->returnValue($data));

        $this->_controller->expects($this->any())->method('_initProductSave')->will($this->returnValue($productMock));

        $this->_controller->saveAction();
    }

    /**
     * Test for Mage_Adminhtml_Catalog_ProductController::quickCreateAction
     */
    public function testQuickCreateActionWithDangerRequest()
    {
        $data = array(
            'entity_id' => 234
        );
        $this->_request->expects($this->any())->method('getParam')->will($this->returnValue($data));

        $typeInstance = $this->getMockBuilder('Mage_Catalog_Model_Product_Type')
            ->setMethods(array('getConfigurableAttributes', 'getEditableAttributes'))->getMock();
        $typeInstance->expects($this->any())->method('getEditableAttributes')->will($this->returnValue(array()));
        $typeInstance->expects($this->any())->method('getConfigurableAttributes')->will($this->returnValue(array()));

        $productMock = $this->getMockBuilder('Mage_Catalog_Model_Product')
            ->setMethods(array('getIdFieldName', 'save', 'getSku', 'isConfigurable', 'setStoreId', 'load', 'setTypeId',
                'setAttributeSetId', 'getAttributeSetId', 'getTypeInstance', 'getWebsiteIds'))
            ->disableOriginalConstructor()
            ->getMock();
        $productMock->expects($this->any())->method('getIdFieldName')->will($this->returnValue('entity_id'));
        $productMock->expects($this->any())->method('isConfigurable')->will($this->returnValue(true));
        $productMock->expects($this->any())->method('setStoreId')->will($this->returnSelf());
        $productMock->expects($this->any())->method('load')->will($this->returnSelf());
        $productMock->expects($this->any())->method('setTypeId')->will($this->returnSelf());
        $productMock->expects($this->any())->method('setAttributeSetId')->will($this->returnSelf());
        $productMock->expects($this->any())->method('getTypeInstance')->will($this->returnValue($typeInstance));
        $productMock->expects($this->never())->method('save');

        $this->_response->expects($this->once())->method('setBody')
            ->with('{"attributes":[],"error":{"message":"Unable to create product"}}');

        $helper = $this->getMockBuilder('Mage_Core_Helper_Data')->setMethods(array('jsonEncode'))->getMock();
        $helper->expects($this->once())->method('jsonEncode')
            ->with(array(
                'attributes' => array(),
                'error' => array(
                    'message' => 'Unable to create product',
                    'fields' => array(
                        'sku' => ''
                    )

                )
            ))
            ->will($this->returnValue('{"attributes":[],"error":{"message":"Unable to create product"}}'));

        $this->_objectManager->expects($this->any())->method('create')
            ->with('Mage_Catalog_Model_Product', array(), true)->will($this->returnValue($productMock));
        $this->_objectManager->expects($this->any())->method('get')
            ->with('Mage_Core_Helper_Data', array())->will($this->returnValue($helper));

        $this->_controller->quickCreateAction();
    }
}
