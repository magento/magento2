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
 * @package     Mage_Checkout
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

require 'Mage/Checkout/controllers/CartController.php';

class Mage_Checkout_CartControllerTest extends PHPUnit_Framework_TestCase
{
    public function testControllerImplementsProductViewInterface()
    {
        $this->assertInstanceOf(
            'Mage_Catalog_Controller_Product_View_Interface',
            $this->getMock('Mage_Checkout_CartController', array(), array(), '', false)
        );
    }

    public function testGoBack()
    {
        $helper = new Magento_Test_Helper_ObjectManager($this);
        $responseMock = $this->getMock('Mage_Core_Controller_Response_Http',
            array('setRedirect'), array(), '', false
        );
        $responseMock->headersSentThrowsException = false;
        $responseMock->expects($this->once())
            ->method('setRedirect')
            ->with('http://some-url/index.php/checkout/cart/')
            ->will($this->returnSelf());

        $requestMock = $this->getMock('Mage_Core_Controller_Request_Http', array(), array(), '', false);
        $requestMock->expects($this->any())->method('getActionName')->will($this->returnValue('add'));
        $requestMock->expects($this->at(0))
            ->method('getParam')->with('return_url')->will($this->returnValue('http://malicious.com/'));
        $requestMock->expects($this->any())
            ->method('getParam')->will($this->returnValue(null));
        $requestMock->expects($this->once())
            ->method('getServer')
            ->with('HTTP_REFERER')
            ->will($this->returnValue('http://some-url/index.php/product.html'));

        $checkoutSessionMock = $this->getMock('Mage_Checkout_Model_Session',
            array('setContinueShoppingUrl'), array(), '', false);
        $checkoutSessionMock->expects($this->once())
            ->method('setContinueShoppingUrl')
            ->with('http://some-url/index.php/product.html')
            ->will($this->returnSelf());

        $sessionMock = $this->getMock('Mage_Core_Model_Session', array(), array(), '', false);

        $urlMock = $this->getMock('Mage_Core_Model_Url',
            array('getUrl'), array(), '', false);
        $urlMock->expects($this->once())
            ->method('getUrl')
            ->with('checkout/cart')
            ->will($this->returnValue('http://some-url/index.php/checkout/cart/'));

        $storeMock = $this->getMock('Mage_Core_Model_Store', array(), array(), '', false);
        $storeMock->expects($this->any())->method('getBaseUrl')->will($this->returnValue('http://some-url/'));
        $storeManager = $this->getMock('Mage_Core_Model_StoreManagerInterface');
        $storeManager->expects($this->any())->method('getStore')->will($this->returnValue($storeMock));

        $objectManager = $this->getMock('Magento_ObjectManager', array(), array(), '', false);
        $objectManager->expects($this->at(0))
            ->method('get')
            ->with($this->equalTo('Mage_Core_Model_StoreManagerInterface'))
            ->will($this->returnValue($storeManager));
        $objectManager->expects($this->at(1))
            ->method('get')
            ->with($this->equalTo('Mage_Core_Model_StoreManagerInterface'))
            ->will($this->returnValue($storeManager));
        $objectManager->expects($this->at(2))
            ->method('get')
            ->with($this->equalTo('Mage_Core_Model_Session'))
            ->will($this->returnValue($sessionMock));
        $objectManager->expects($this->at(3))
            ->method('create')
            ->with($this->equalTo('Mage_Core_Model_Url'))
            ->will($this->returnValue($urlMock));

        $configMock = $this->getMock('Mage_Core_Model_Store_Config', array('getConfig'), array(), '', false);
        $configMock->expects($this->once())
            ->method('getConfig')
            ->with('checkout/cart/redirect_to_cart')
            ->will($this->returnValue('1'));

        $arguments = array(
            'response' => $responseMock,
            'request' => $requestMock,
            'objectManager' => $objectManager,
            'checkoutSession' => $checkoutSessionMock,
            'storeConfig' => $configMock,
        );

        $controller = $helper->getObject('Mage_Checkout_CartController', $arguments);

        $reflectionObject = new ReflectionObject($controller);
        $reflectionMethod = $reflectionObject->getMethod('_goBack');
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->invoke($controller);
    }
}
