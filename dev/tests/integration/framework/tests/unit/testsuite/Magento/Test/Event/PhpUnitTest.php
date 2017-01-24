<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
            \Magento\TestFramework\EventManager::class,
            ['fireEvent'],
            [[]]
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
     * @expectedException \Magento\Framework\Exception\LocalizedException
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
        return [
            'method "addError"' => ['addError'],
            'method "addFailure"' => ['addFailure'],
            'method "addIncompleteTest"' => ['addIncompleteTest'],
            'method "addSkippedTest"' => ['addSkippedTest']
        ];
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
        $this->_object->startTest($this->getMock(\PHPUnit_Framework_Test::class));
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
        $this->_object->endTest($this->getMock(\PHPUnit_Framework_Test::class), 0);
    }
}
