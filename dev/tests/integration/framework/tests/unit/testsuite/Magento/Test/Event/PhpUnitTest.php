<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\TestFramework\Event\PhpUnit.
 */
namespace Magento\Test\Event;

use PHPUnit\Framework\TestSuite;

class PhpUnitTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\TestFramework\Event\PhpUnit
     */
    protected $_object;

    /**
     * @var \Magento\TestFramework\EventManager|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_eventManager;

    protected function setUp(): void
    {
        $this->_eventManager = $this->getMockBuilder(\Magento\TestFramework\EventManager::class)
            ->onlyMethods(['fireEvent'])
            ->setConstructorArgs([[]])
            ->getMock();
        $this->_object = new \Magento\TestFramework\Event\PhpUnit($this->_eventManager);
    }

    protected function tearDown(): void
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
     */
    public function testConstructorException()
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);

        new \Magento\TestFramework\Event\Magento();
    }

    /**
     * @param string $method
     * @dataProvider doNotFireEventDataProvider
     */
    public function testDoNotFireEvent($method)
    {
        $this->_eventManager->expects($this->never())->method('fireEvent');
        $this->_object->{$method}($this, new \PHPUnit\Framework\AssertionFailedError(), 0);
    }

    public static function doNotFireEventDataProvider()
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
        $this->_object->startTestSuite(TestSuite::empty('TestSuite'));
    }
    public function testEndTestSuiteFireEvent()
    {
        $this->_eventManager->expects($this->once())->method('fireEvent')->with('endTestSuite');
        $this->_object->endTestSuite(TestSuite::empty('TestSuite'));
    }

    public function testStartTestFireEvent()
    {
        $this->_eventManager->expects($this->once())->method('fireEvent')->with('startTest');
        $this->_object->startTest($this);
    }

    public function testStartTestDoNotFireEvent()
    {
        $this->_eventManager->expects($this->never())->method('fireEvent');
        $this->_object->startTest($this->createMock(\PHPUnit\Framework\Test::class));
    }

    public function testEndTestFireEvent()
    {
        $this->_eventManager->expects($this->once())->method('fireEvent')->with('endTest');
        $this->_object->endTest($this, 0);
    }

    public function testEndTestDoNotFireEvent()
    {
        $this->_eventManager->expects($this->never())->method('fireEvent');
        $this->_object->endTest($this->createMock(\PHPUnit\Framework\Test::class), 0);
    }
}
