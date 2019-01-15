<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\TestFramework\Event\Transaction.
 */
namespace Magento\Test\Event;

class TransactionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\TestFramework\Event\Transaction|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_object;

    /**
     * @var \Magento\TestFramework\EventManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_eventManager;

    /**
     * @var \Magento\TestFramework\Db\Adapter\TransactionInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_adapter;

    protected function setUp()
    {
        $this->_eventManager = $this->getMockBuilder(\Magento\TestFramework\EventManager::class)
            ->setMethods(['fireEvent'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->_adapter =
            $this->createPartialMock(\Magento\TestFramework\Db\Adapter\Mysql::class, ['beginTransaction', 'rollBack']);
        $this->_object = $this->getMockBuilder(\Magento\TestFramework\Event\Transaction::class)
            ->setMethods(['_getConnection'])
            ->setConstructorArgs([$this->_eventManager])
            ->getMock();

        $this->_object->expects($this->any())->method('_getConnection')->will($this->returnValue($this->_adapter));
    }

    /**
     * Imitate transaction start request
     *
     * @param string $eventName
     */
    protected function _imitateTransactionStartRequest($eventName)
    {
        $callback = function ($eventName, array $parameters) {
            /** @var $param \Magento\TestFramework\Event\Param\Transaction */
            $param = $parameters[1];
            $param->requestTransactionStart();
        };
        $this->_eventManager->expects(
            $this->at(0)
        )->method(
            'fireEvent'
        )->with(
            $eventName
        )->will(
            $this->returnCallback($callback)
        );
    }

    /**
     * Setup expectations for "transaction start" use case
     *
     * @param \PHPUnit\Framework\MockObject\Matcher\Invocation $invocationMatcher
     */
    protected function _expectTransactionStart(\PHPUnit\Framework\MockObject\Matcher\Invocation $invocationMatcher)
    {
        $this->_eventManager->expects($invocationMatcher)->method('fireEvent')->with('startTransaction');
        $this->_adapter->expects($this->once())->method('beginTransaction');
    }

    /**
     * Imitate transaction rollback request
     *
     * @param string $eventName
     */
    protected function _imitateTransactionRollbackRequest($eventName)
    {
        $callback = function ($eventName, array $parameters) {
            /** @var $param \Magento\TestFramework\Event\Param\Transaction */
            $param = $parameters[1];
            $param->requestTransactionRollback();
        };
        $this->_eventManager->expects(
            $this->at(0)
        )->method(
            'fireEvent'
        )->with(
            $eventName
        )->will(
            $this->returnCallback($callback)
        );
    }

    /**
     * Setup expectations for "transaction rollback" use case
     *
     * @param \PHPUnit\Framework\MockObject\Matcher\Invocation $invocationMatcher
     */
    protected function _expectTransactionRollback(\PHPUnit\Framework\MockObject\Matcher\Invocation $invocationMatcher)
    {
        $this->_eventManager->expects($invocationMatcher)->method('fireEvent')->with('rollbackTransaction');
        $this->_adapter->expects($this->once())->method('rollback');
    }

    /**
     * @param string $method
     * @param string $eventName
     * @dataProvider startAndRollbackTransactionDataProvider
     */
    public function testStartAndRollbackTransaction($method, $eventName)
    {
        $this->_imitateTransactionStartRequest($eventName);
        $this->_expectTransactionStart($this->at(1));
        $this->_object->{$method}($this);

        $this->_imitateTransactionRollbackRequest($eventName);
        $this->_expectTransactionRollback($this->at(1));
        $this->_object->{$method}($this);
    }

    public function startAndRollbackTransactionDataProvider()
    {
        return [
            'method "startTest"' => ['startTest', 'startTestTransactionRequest'],
            'method "endTest"' => ['endTest', 'endTestTransactionRequest']
        ];
    }

    /**
     * @param string $method
     * @param string $eventName
     * @dataProvider startAndRollbackTransactionDataProvider
     */
    public function testDoNotStartAndRollbackTransaction($method, $eventName)
    {
        $this->_eventManager->expects($this->once())->method('fireEvent')->with($eventName);
        $this->_adapter->expects($this->never())->method($this->anything());
        $this->_object->{$method}($this);
    }

    public function testEndTestSuiteDoNothing()
    {
        $this->_eventManager->expects($this->never())->method('fireEvent');
        $this->_adapter->expects($this->never())->method($this->anything());
        $this->_object->endTestSuite();
    }

    public function testEndTestSuiteRollbackTransaction()
    {
        $this->_imitateTransactionStartRequest('startTestTransactionRequest');
        $this->_object->startTest($this);

        $this->_expectTransactionRollback($this->once());
        $this->_object->endTestSuite();
    }
}
