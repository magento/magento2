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

/**
 * Test class for Magento_Test_Listener_Annotation_Isolation.
 */
class Magento_Test_Listener_Annotation_IsolationTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Magento_Test_Listener
     */
    protected $_listener;

    /**
     * @var Magento_Test_Listener_Annotation_Isolation|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_annotation;

    protected function setUp()
    {
        $this->_listener = new Magento_Test_Listener;
        $this->_listener->startTest($this);

        $this->_annotation = $this->getMock(
            'Magento_Test_Listener_Annotation_Isolation',
            array('_isolateApp'),
            array($this->_listener)
        );
    }

    protected function tearDown()
    {
        /*
         * If an exception is thrown by a listener on 'startTest' event,
         * 'setUp' method won't be executed and there will be nothing to cleanup
         */
        if ($this->_listener) {
            $this->_listener->endTest($this->_listener->getCurrentTest(), 0);
        }
    }

    public function testStartTestSuite()
    {
        $this->_annotation->expects($this->once())->method('_isolateApp');
        $this->_annotation->startTestSuite();
    }

    /**
     * @magentoAppIsolation invalid
     * @expectedException Magento_Exception
     */
    public function testEndTestIsolationInvalid()
    {
        $this->_annotation->endTest();
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoAppIsolation disabled
     * @expectedException Magento_Exception
     */
    public function testEndTestIsolationAmbiguous()
    {
        $this->_annotation->endTest();
    }

    public function testEndTestIsolationDefault()
    {
        $this->_annotation->expects($this->never())->method('_isolateApp');
        $this->_annotation->endTest();
    }

    public function testEndTestIsolationController()
    {
        /** @var $controllerTestCase Magento_Test_TestCase_ControllerAbstract */
        $controllerTestCase = $this->getMockForAbstractClass('Magento_Test_TestCase_ControllerAbstract');
        $this->_listener->startTest($controllerTestCase);
        $this->_annotation->expects($this->once())->method('_isolateApp');
        $this->_annotation->endTest();
    }

    /**
     * @magentoAppIsolation disabled
     */
    public function testEndTestIsolationDisabled()
    {
        $this->_annotation->expects($this->never())->method('_isolateApp');
        $this->_annotation->endTest();
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testEndTestIsolationEnabled()
    {
        $this->_annotation->expects($this->once())->method('_isolateApp');
        $this->_annotation->endTest();
    }
}
