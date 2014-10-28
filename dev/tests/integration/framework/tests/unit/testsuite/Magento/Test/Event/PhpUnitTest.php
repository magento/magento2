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
 * Test class for \Magento\TestFramework\Event\PhpUnit.
 */
namespace Magento\Test\Event;

class PhpUnitTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Event\PhpUnit
     */
    protected $_object;

    /**
     * @var \Magento\TestFramework\EventManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_eventManager;

    protected function setUp()
    {
        $this->_eventManager = $this->getMock(
            'Magento\TestFramework\EventManager',
            array('fireEvent'),
            array(array())
        );
        $this->_object = new \Magento\TestFramework\Event\PhpUnit($this->_eventManager);
    }

    protected function tearDown()
    {
        \Magento\TestFramework\Event\PhpUnit::setDefaultEventManager(null);
    }

    public function testConstructorDefaultEventManager()
    {
        \Magento\TestFramework\Event\PhpUnit::setDefaultEventManager($this->_eventManager);
        $this->_object = new \Magento\TestFramework\Event\PhpUnit();
        $this->testStartTestSuiteFireEvent();
    }

    /**
     * @expectedException \Magento\Framework\Exception
     */
    public function testConstructorException()
    {
        new \Magento\TestFramework\Event\Magento();
    }

    /**
     * @param string $method
     * @dataProvider doNotFireEventDataProvider
     */
    public function testDoNotFireEvent($method)
    {
        $this->_eventManager->expects($this->never())->method('fireEvent');
        $this->_object->{$method}($this, new \PHPUnit_Framework_AssertionFailedError(), 0);
    }

    public function doNotFireEventDataProvider()
    {
        return array(
            'method "addError"' => array('addError'),
            'method "addFailure"' => array('addFailure'),
            'method "addIncompleteTest"' => array('addIncompleteTest'),
            'method "addSkippedTest"' => array('addSkippedTest')
        );
    }

    public function testStartTestSuiteFireEvent()
    {
        $this->_eventManager->expects($this->once())->method('fireEvent')->with('startTestSuite');
        $this->_object->startTestSuite(new \PHPUnit_Framework_TestSuite());
    }

    public function testStartTestSuiteDoNotFireEvent()
    {
        $this->_eventManager->expects($this->never())->method('fireEvent');
        $this->_object->startTestSuite(new \PHPUnit_Framework_TestSuite_DataProvider());
    }

    public function testEndTestSuiteFireEvent()
    {
        $this->_eventManager->expects($this->once())->method('fireEvent')->with('endTestSuite');
        $this->_object->endTestSuite(new \PHPUnit_Framework_TestSuite());
    }

    public function testEndTestSuiteDoNotFireEvent()
    {
        $this->_eventManager->expects($this->never())->method('fireEvent');
        $this->_object->endTestSuite(new \PHPUnit_Framework_TestSuite_DataProvider());
    }

    public function testStartTestFireEvent()
    {
        $this->_eventManager->expects($this->once())->method('fireEvent')->with('startTest');
        $this->_object->startTest($this);
    }

    public function testStartTestDoNotFireEvent()
    {
        $this->_eventManager->expects($this->never())->method('fireEvent');
        $this->_object->startTest(new \PHPUnit_Framework_Warning());
        $this->_object->startTest($this->getMock('PHPUnit_Framework_Test'));
    }

    public function testEndTestFireEvent()
    {
        $this->_eventManager->expects($this->once())->method('fireEvent')->with('endTest');
        $this->_object->endTest($this, 0);
    }

    public function testEndTestDoNotFireEvent()
    {
        $this->_eventManager->expects($this->never())->method('fireEvent');
        $this->_object->endTest(new \PHPUnit_Framework_Warning(), 0);
        $this->_object->endTest($this->getMock('PHPUnit_Framework_Test'), 0);
    }
}
