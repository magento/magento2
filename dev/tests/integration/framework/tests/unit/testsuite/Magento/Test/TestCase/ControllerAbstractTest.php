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
 * @package     Magento
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Magento_Test_TestCase_ControllerAbstractTest extends Magento_Test_TestCase_ControllerAbstract
{
    protected $_bootstrap;

    /**
     * Bootstrap instance getter.
     * Mocking real bootstrap
     *
     * @return Magento_Test_Bootstrap
     */
    protected function _getBootstrap()
    {
        if (!$this->_bootstrap) {
            $this->_bootstrap = $this->getMock('Magento_Test_Bootstrap', array('getAllOptions'), array(), '', false);
        }
        return $this->_bootstrap;
    }

    public function testSetUp()
    {
        $this->assertInternalType('array', $this->_runOptions);
        $this->assertArrayHasKey('request', $this->_runOptions);
        $this->assertInstanceOf('Magento_Test_Request', $this->_runOptions['request']);
        $this->assertArrayHasKey('response', $this->_runOptions);
        $this->assertInstanceOf('Magento_Test_Response', $this->_runOptions['response']);
    }

    public function testGetRequest()
    {
        $request = $this->getRequest();
        $this->assertInstanceOf('Magento_Test_Request', $request);
        $this->assertSame($request, $this->getRequest());
    }

    public function testGetResponse()
    {
        $response = $this->getResponse();
        $this->assertInstanceOf('Magento_Test_Response', $response);
        $this->assertSame($response, $this->getResponse());
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function testAssert404NotFound()
    {
        $this->getRequest()->setActionName('noRoute');
        $this->getResponse()->setBody(
            '404 Not Found test <h3>We are sorry, but the page you are looking for cannot be found.</h3>'
        );
        $this->assert404NotFound();

        $this->getResponse()->setBody('');
        try {
            $this->assert404NotFound();
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail('Failed response body validation');
    }

    /**
     * @expectedException PHPUnit_Framework_AssertionFailedError
     */
    public function testAssertRedirectFailure()
    {
        $this->assertRedirect();
    }

    /**
     * @depends testAssertRedirectFailure
     */
    public function testAssertRedirect()
    {
        /*
         * Prevent calling Mage_Core_Controller_Response_Http::setRedirect() because it executes Mage::dispatchEvent(),
         * which requires fully initialized application environment intentionally not available for unit tests
         */
        $setRedirectMethod = new ReflectionMethod('Zend_Controller_Response_Http', 'setRedirect');
        $setRedirectMethod->invoke($this->getResponse(), 'http://magentocommerce.com');
        $this->assertRedirect();
        $this->assertRedirect('http://magentocommerce.com');
    }
}
